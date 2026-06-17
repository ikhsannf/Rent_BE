<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreBookingRequest $request)
    {
        try {
            $listing = Listing::findOrFail($request->listing_id);
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $days = $start->diffInDays($end) + 1;

            $totalPrice = $days * $listing->price_per_day;

            $booking = new Booking([
                'booking_code'    => Booking::generateBookingCode(),
                'borrower_id'     => $request->user()->id,
                'lender_id'       => $listing->user_id,
                'listing_id'      => $listing->id,
                'start_date'      => $start,
                'end_date'        => $end,
                'total_days'      => $days,
                'price_per_day'   => $listing->price_per_day,
                'total_price'     => $totalPrice,
                'deposit_amount'  => $listing->deposit ?? 0,
                'status'          => 'pending',
                'notes'           => $request->notes,
            ]);

            $booking->calculateFinancials();
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data'    => new BookingResource($booking)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myBookings(Request $request)
    {
        try {
            $status = $request->query('status');
            $bookings = Booking::forUser($request->user())
                ->when($status, fn($q) => $q->where('status', $status))
                ->with(['listing', 'lender', 'borrower'])
                ->latest()
                ->paginate($request->per_page ?? 10);

            return BookingResource::collection($bookings)->additional([
                'success' => true,
                'message' => 'Bookings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bookings'
            ], 500);
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['listing', 'lender', 'borrower']);

        return response()->json([
            'success' => true,
            'message' => 'Booking details retrieved',
            'data'    => new BookingResource($booking)
        ]);
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking)
    {
        $this->authorize('updateStatus', $booking);
        $user = $request->user();
        $newStatus = $request->status;

        // Simple validation logic for roles
        if (in_array($newStatus, ['approved', 'rejected', 'ongoing', 'completed']) && !$user->isLender()) {
            return response()->json(['success' => false, 'message' => 'Only lender can set this status'], 403);
        }
        if ($newStatus === 'cancelled' && !$user->isBorrower()) {
            return response()->json(['success' => false, 'message' => 'Only borrower can cancel'], 403);
        }

        try {
            $booking->status = $newStatus;
            
            if ($newStatus === 'approved') $booking->approved_at = now();
            if ($newStatus === 'ongoing') {
                $booking->started_at = now();
                $booking->listing->update(['status' => 'rented']);
            }
            if ($newStatus === 'completed') {
                $booking->completed_at = now();
                $booking->listing->increment('total_bookings');
                // Check other ongoing bookings for this listing
                $this->checkListingAvailability($booking->listing);
            }
            if ($newStatus === 'cancelled') {
                $booking->cancelled_at = now();
                $this->checkListingAvailability($booking->listing);
            }
            if ($newStatus === 'rejected') {
                $booking->rejection_reason = $request->rejection_reason;
            }

            $booking->save();

            // Notify other party (Stub for now as real notification needs setup)
            // $otherParty = ($user->id === $booking->borrower_id) ? $booking->lender : $booking->borrower;
            // $otherParty->notify(new BookingStatusChanged($booking));

            return response()->json([
                'success' => true,
                'message' => "Booking status updated to {$newStatus}",
                'data'    => new BookingResource($booking->load('listing'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadPaymentProof(Request $request, Booking $booking)
    {
        $request->validate(['payment_proof' => 'required|image|max:2048']);

        try {
            $path = $request->file('payment_proof')->store("payments/{$booking->booking_code}", 'public');
            $booking->update([
                'payment_proof'  => $path,
                'payment_status' => 'paid'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'data'    => new BookingResource($booking)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed'
            ], 500);
        }
    }

    private function checkListingAvailability(Listing $listing)
    {
        $hasOngoing = Booking::where('listing_id', $listing->id)
            ->whereIn('status', ['ongoing'])
            ->exists();
        
        if (!$hasOngoing) {
            $listing->update(['status' => 'available']);
        }
    }
}
