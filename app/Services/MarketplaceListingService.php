<?php

namespace App\Services;

use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MarketplaceListingResource;

class MarketplaceListingService
{
    public function index()
    {
        return MarketplaceListingResource::collection(
            MarketplaceListing::with(['category', 'user'])->get()
        );
    }

    public function store($user, $validated)
    {
        
        // Map product_name to title
        $data = $validated;
        $data['title'] = $data['product_name'];
        unset($data['product_name']);

        $data['user_id'] = $user->id;

        // Handle media_files upload
        $mediaUrls = [];
        if (isset($data['media_files']) && is_array($data['media_files'])) {
            foreach ($data['media_files'] as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('marketplace_media', $filename, 'public');
                $mediaUrls[] = $path;
            }
        }
        $data['media_urls'] = $mediaUrls;
        unset($data['media_files']);

        $listing = MarketplaceListing::create($data);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'Listing created successfully',
            'data' => $listing
        ], 201);
    }

  public function show(MarketplaceListing $marketplaceListing)
{
    $marketplaceListing->load(['category', 'user']);
    return new MarketplaceListingResource($marketplaceListing);
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
