<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use App\Http\Requests\StoreReelRequest;
use App\Http\Requests\UpdateReelRequest;
use App\Services\ReelService;

class ReelController extends Controller
{
    protected ReelService $reelService;

    public function __construct(ReelService $reelService)
    {
        $this->reelService = $reelService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->reelService->index();
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
    public function store(StoreReelRequest $request)
    {
        return $this->reelService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Reel $reel)
    {
        return $this->reelService->show($reel);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reel $reel)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReelRequest $request, Reel $reel)
    {
        return $this->reelService->update($reel, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reel $reel)
    {
        return $this->reelService->destroy($reel);
    }
}
