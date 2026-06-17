<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request)
    {
        try {
            $booking = Booking::findOrFail($request->booking_id);
            $user = $request->user();

            if ($booking->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Review can only be made for completed bookings'], 422);
            }

            // Determine type and check if already reviewed
            $type = '';
            $revieweeId = null;

            if ($user->id === $booking->borrower_id) {
                if ($booking->borrower_reviewed) {
                    return response()->json(['success' => false, 'message' => 'You have already reviewed this booking'], 422);
                }
                $type = 'borrower_to_lender';
                $revieweeId = $booking->lender_id;
            } elseif ($user->id === $booking->lender_id) {
                if ($booking->lender_reviewed) {
                    return response()->json(['success' => false, 'message' => 'You have already reviewed this booking'], 422);
                }
                $type = 'lender_to_borrower';
                $revieweeId = $booking->borrower_id;
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized to review this booking'], 403);
            }

            $review = Review::create([
                'booking_id'  => $booking->id,
                'reviewer_id' => $user->id,
                'reviewee_id' => $revieweeId,
                'listing_id'  => $booking->listing_id,
                'type'        => $type,
                'rating'      => $request->rating,
                'comment'     => $request->comment,
            ]);

            // Update flag di booking
            if ($type === 'borrower_to_lender') {
                $booking->update(['borrower_reviewed' => true]);
            } else {
                $booking->update(['lender_reviewed' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data'    => new ReviewResource($review->load('reviewer'))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listingReviews(Listing $listing)
    {
        try {
            $reviews = $listing->reviews()
                ->where('type', 'borrower_to_lender')
                ->with('reviewer')
                ->latest()
                ->paginate(10);

            return ReviewResource::collection($reviews)->additional([
                'success' => true,
                'message' => 'Listing reviews retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews'
            ], 500);
        }
    }
}
