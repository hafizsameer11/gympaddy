<?php

namespace App\Services;

use App\Models\MarketplaceListing;

class MarketplaceListingService
{
    public function index()
    {
        return MarketplaceListing::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $listing = MarketplaceListing::create($data);
        return response()->json($listing, 201);
    }

    public function show(MarketplaceListing $marketplaceListing)
    {
        return $marketplaceListing;
    }

    public function update(MarketplaceListing $marketplaceListing, $validated)
    {
        $marketplaceListing->update($validated);
        return response()->json($marketplaceListing);
    }

    public function destroy(MarketplaceListing $marketplaceListing)
    {
        $marketplaceListing->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
