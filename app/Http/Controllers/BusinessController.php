<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Services\BusinessService;

class BusinessController extends Controller
{
    protected BusinessService $businessService;

    public function __construct(BusinessService $businessService)
    {
        $this->businessService = $businessService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->businessService->index();
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
    public function store(StoreBusinessRequest $request)
    {
        return $this->businessService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        return $this->businessService->show($business);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBusinessRequest $request, Business $business)
    {
        return $this->businessService->update($business, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        return $this->businessService->destroy($business);
    }
}