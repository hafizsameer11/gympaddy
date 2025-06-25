<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCategory;
use App\Http\Requests\StoreMarketplaceCategoryRequest;
use App\Http\Requests\UpdateMarketplaceCategoryRequest;
use App\Services\MarketplaceCategoryService;

class MarketplaceCategoryController extends Controller
{
    protected MarketplaceCategoryService $marketplaceCategoryService;

    public function __construct(MarketplaceCategoryService $marketplaceCategoryService)
    {
        $this->marketplaceCategoryService = $marketplaceCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->marketplaceCategoryService->index();
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
    public function store(StoreMarketplaceCategoryRequest $request)
    {
        return $this->marketplaceCategoryService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketplaceCategory $marketplaceCategory)
    {
        return $this->marketplaceCategoryService->show($marketplaceCategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketplaceCategory $marketplaceCategory)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMarketplaceCategoryRequest $request, MarketplaceCategory $marketplaceCategory)
    {
        return $this->marketplaceCategoryService->update($marketplaceCategory, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketplaceCategory $marketplaceCategory)
    {
        return $this->marketplaceCategoryService->destroy($marketplaceCategory);
    }
}
      