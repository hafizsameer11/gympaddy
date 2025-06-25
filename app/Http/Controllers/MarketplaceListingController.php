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
        return $this->marketplaceListingService->update($marketplaceListing, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketplaceListing $marketplaceListing)
    {
        return $this->marketplaceListingService->destroy($marketplaceListing);
    }
}
