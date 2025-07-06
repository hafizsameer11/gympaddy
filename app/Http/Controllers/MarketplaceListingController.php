<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Http\Requests\StoreMarketplaceListingRequest;
use App\Http\Requests\UpdateMarketplaceListingRequest;
use App\Services\MarketplaceListingService;

class MarketplaceListingController extends Controller
{
    protected MarketplaceListingService $marketplaceListingService;

    public function __construct(MarketplaceListingService $marketplaceListingService)
    {
        $this->marketplaceListingService = $marketplaceListingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->marketplaceListingService->index();
    }

    public function latest()
    {
        return $this->marketplaceListingService->latest();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMarketplaceListingRequest $request)
    {
        return $this->marketplaceListingService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketplaceListing $marketplaceListing)
    {
        return $this->marketplaceListingService->show($marketplaceListing);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketplaceListing $marketplaceListing)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(UpdateMarketplaceListingRequest $request, MarketplaceListing $marketplaceListing)
{
    $validated = $request->validated();
    $data = $validated;

    // Allow client to pass which images to keep
    $existingMedia = $request->input('existing_media', []); // Array of existing image paths

    // Handle new image uploads (same as in store)
    $mediaUrls = [];
    if ($request->hasFile('media_files')) {
        foreach ($request->file('media_files') as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('marketplace_media', $filename, 'public');
            $mediaUrls[] = $path;
        }
    }

    // Merge old images the user kept with newly uploaded ones
    $mergedMedia = array_merge($existingMedia, $mediaUrls);
    $data['media_urls'] = $mergedMedia;

    // Laravel will cast to array if you set $casts['media_urls'] = 'array' in model

    // Actually update the record
    $marketplaceListing->update($data);

    return response()->json([
        'status' => 'success',
        'code' => 200,
        'message' => 'Listing updated successfully',
        'data' => $marketplaceListing
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketplaceListing $marketplaceListing)
    {
        return $this->marketplaceListingService->destroy($marketplaceListing);
    }

    public function userListings($user_id)
    {
        return $this->marketplaceListingService->getForUser($user_id);
    }
}
