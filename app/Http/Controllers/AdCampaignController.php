<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Http\Requests\StoreAdCampaignRequest;
use App\Http\Requests\UpdateAdCampaignRequest;
use App\Services\AdCampaignService;

class AdCampaignController extends Controller
{
    protected AdCampaignService $adCampaignService;

    public function __construct(AdCampaignService $adCampaignService)
    {
        $this->adCampaignService = $adCampaignService;
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return $this->adCampaignService->index();
    // }
    public function index()
    {
        return $this->adCampaignService->getBoostedCampaigns();
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
    public function store(StoreAdCampaignRequest $request)
    {
        return $this->adCampaignService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(AdCampaign $adCampaign)
    {
        return $this->adCampaignService->show($adCampaign);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdCampaign $adCampaign)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdCampaignRequest $request, AdCampaign $adCampaign)
    {
        return $this->adCampaignService->update($adCampaign, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdCampaign $adCampaign)
    {
        return $this->adCampaignService->destroy($adCampaign);
    }
}
