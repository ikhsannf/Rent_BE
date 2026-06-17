<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Http\Resources\ReviewResource;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ListingController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'search', 'category_id', 'min_price', 'max_price', 
                'min_rating', 'location', 'condition'
            ]);

            $listings = Listing::available()
                ->filter($filters)
                ->with(['lender', 'category', 'photos'])
                ->paginate($request->per_page ?? 10);

            return ListingResource::collection($listings)->additional([
                'success' => true,
                'message' => 'Listings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve listings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Listing $listing)
    {
        $listing->load(['lender', 'category', 'photos']);
        $reviews = $listing->reviews()->with('reviewer')->latest()->limit(5)->get();

        return (new ListingResource($listing))->additional([
            'success' => true,
            'message' => 'Listing retrieved successfully',
            'reviews' => ReviewResource::collection($reviews)
        ]);
    }

    public function store(StoreListingRequest $request)
    {
        try {
            $listing = Listing::create([
                'user_id'       => $request->user()->id,
                'category_id'   => $request->category_id,
                'title'         => $request->title,
                'description'   => $request->description,
                'price_per_day' => $request->price_per_day,
                'deposit'       => $request->deposit,
                'condition'     => $request->condition,
                'location'      => $request->location,
                'brand'         => $request->brand,
                'model'         => $request->model,
                'min_rent_days' => $request->min_rent_days ?? 1,
                'max_rent_days' => $request->max_rent_days,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store("listings/{$listing->id}", 'public');
                    ListingPhoto::create([
                        'listing_id' => $listing->id,
                        'path'       => $path,
                        'url'        => Storage::disk('public')->url($path),
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully',
                'data'    => new ListingResource($listing->load('photos'))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create listing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateListingRequest $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        try {
            $listing->update($request->validated());

            if ($request->hasFile('photos')) {
                // Untuk demo ini, kita tambahkan foto baru (bukan replace semua)
                // Di aplikasi real, mungkin ada endpoint khusus hapus foto
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store("listings/{$listing->id}", 'public');
                    ListingPhoto::create([
                        'listing_id' => $listing->id,
                        'path'       => $path,
                        'url'        => Storage::disk('public')->url($path),
                        'is_primary' => false,
                        'sort_order' => $listing->photos()->count(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully',
                'data'    => new ListingResource($listing->load('photos'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        try {
            // Soft delete will handle DB, let's just delete files if it's permanent or just leave it
            // Prompt says: soft delete listing dan foto-fotonya, hapus file dari storage
            foreach ($listing->photos as $photo) {
                Storage::disk('public')->delete($photo->path);
            }
            $listing->photos()->delete();
            $listing->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing'
            ], 500);
        }
    }

    public function myListings(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->with(['category', 'photos'])
            ->paginate($request->per_page ?? 10);

        return ListingResource::collection($listings)->additional([
            'success' => true,
            'message' => 'Your listings retrieved successfully'
        ]);
    }
}
