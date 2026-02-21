<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    private function formatListing(MarketplaceListing $listing): array
    {
        $user = $listing->relationLoaded('user') ? $listing->user : null;
        $category = $listing->relationLoaded('category') ? $listing->category : null;

        $images = [];
        if (!empty($listing->media_urls)) {
            $raw = is_string($listing->media_urls) ? json_decode($listing->media_urls, true) : $listing->media_urls;
            $images = is_array($raw) ? $raw : [];
        }
        if (empty($images) && !empty($listing->images)) {
            $raw = is_string($listing->images) ? json_decode($listing->images, true) : $listing->images;
            $images = is_array($raw) ? $raw : [];
        }
        if (empty($images) && !empty($listing->image)) {
            $images = [$listing->image];
        }

        return [
            'id'            => (string) $listing->id,
            'name'          => $listing->title ?? $listing->name ?? '—',
            'description'   => $listing->description ?? '',
            'price'         => (float) ($listing->price ?? 0),
            'category'      => $category->name ?? $listing->category ?? '—',
            'status'        => $listing->status ?? 'active',
            'boostedStatus' => $listing->is_featured ? 'boosted' : 'normal',
            'images'        => $images,
            'image'         => $images[0] ?? null,
            'location'      => $listing->location ?? '—',
            'userName'      => $user->fullname ?? 'Unknown',
            'username'      => $user->username ?? '',
            'userImage'     => $user->profile_picture ?? null,
            'createdAt'     => $listing->created_at->toIso8601String(),
            'date'          => $listing->created_at->format('d/m/y'),
        ];
    }

    public function getAllListings(Request $request)
    {
        try {
            $query = MarketplaceListing::with(['user:id,username,fullname,profile_picture', 'category']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 50);

            $listings = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formattedListings = collect($listings->items())->map(fn($l) => $this->formatListing($l));

            return response()->json([
                'success' => true,
                'data' => [
                    'listings' => $formattedListings,
                    'pagination' => [
                        'currentPage' => $listings->currentPage(),
                        'totalPages' => $listings->lastPage(),
                        'totalItems' => $listings->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getListingById($id)
    {
        try {
            $listing = MarketplaceListing::with(['user:id,username,fullname,email,profile_picture', 'category'])->find($id);
            if (!$listing) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Listing not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $this->formatListing($listing)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserListings($userId)
    {
        try {
            $listings = MarketplaceListing::where('user_id', $userId)
                ->with(['user:id,username,fullname,profile_picture', 'category'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($l) => $this->formatListing($l));

            return response()->json(['success' => true, 'data' => $listings]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createListing(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'category' => 'required|string',
            ]);

            $listing = MarketplaceListing::create($validated);
            return response()->json(['success' => true, 'message' => 'Listing created successfully', 'data' => $listing]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateListing(Request $request, $id)
    {
        try {
            $listing = MarketplaceListing::find($id);
            if (!$listing) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Listing not found']], 404);
            }

            $updateData = [];
            if ($request->has('name'))        $updateData['title']       = $request->name;
            if ($request->has('title'))       $updateData['title']       = $request->title;
            if ($request->has('description')) $updateData['description'] = $request->description;
            if ($request->has('price'))       $updateData['price']       = $request->price;
            if ($request->has('category')) {
                $category = \App\Models\MarketplaceCategory::where('name', $request->category)->first();
                if ($category) {
                    $updateData['category_id'] = $category->id;
                }
            }
            if ($request->has('location'))    $updateData['location']    = $request->location;
            if ($request->has('status'))      $updateData['status']      = $request->status;

            $listing->update($updateData);

            $listing->load(['user:id,username,fullname,profile_picture', 'category']);
            return response()->json(['success' => true, 'message' => 'Listing updated successfully', 'data' => $this->formatListing($listing)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteListing($id)
    {
        try {
            $listing = MarketplaceListing::find($id);
            if (!$listing) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Listing not found']], 404);
            }
            $listing->delete();
            return response()->json(['success' => true, 'message' => 'Listing deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function boostListing(Request $request, $id)
    {
        try {
            $listing = MarketplaceListing::find($id);
            if (!$listing) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Listing not found']], 404);
            }
            $listing->update(['is_featured' => true]);
            return response()->json(['success' => true, 'message' => 'Listing boosted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getMarketStats()
    {
        try {
            $totalListings = MarketplaceListing::count();
            $activeListings = MarketplaceListing::where('status', 'active')->count();
            $boostedListings = MarketplaceListing::where('is_featured', true)->count();
            $totalRevenue = MarketplaceListing::where('status', 'sold')->sum('price');

            return response()->json([
                'success' => true,
                'data' => [
                    'totalListings' => $totalListings,
                    'activeListings' => $activeListings,
                    'boostedListings' => $boostedListings,
                    'totalRevenue' => (float) $totalRevenue,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
