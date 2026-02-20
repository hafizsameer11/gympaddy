<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function getAllListings(Request $request)
    {
        try {
            $query = MarketplaceListing::with('user:id,username,fullname,profile_picture');

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('boosted') && $request->boosted !== 'all') {
                $boosted = $request->boosted === 'true';
                $query->where('is_boosted', $boosted);
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $listings = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'listings' => $listings->items(),
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
            $listing = MarketplaceListing::with('user:id,username,fullname,email,profile_picture')->find($id);
            if (!$listing) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Listing not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $listing]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getUserListings($userId)
    {
        try {
            $listings = MarketplaceListing::where('user_id', $userId)->get();
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
            $listing->update($request->all());
            return response()->json(['success' => true, 'message' => 'Listing updated successfully']);
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
            $listing->update(['is_boosted' => true, 'boost_duration' => $request->duration ?? 7]);
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
            $boostedListings = MarketplaceListing::where('is_boosted', true)->count();
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
