<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Http\Requests\StoreLiveStreamRequest;
use App\Http\Requests\UpdateLiveStreamRequest;
use App\Services\LiveStreamService;

class LiveStreamController extends Controller
{
    protected LiveStreamService $liveStreamService;

    public function __construct(LiveStreamService $liveStreamService)
    {
        $this->liveStreamService = $liveStreamService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->liveStreamService->index();
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
    public function store(StoreLiveStreamRequest $request)
    {
        return $this->liveStreamService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveStream $liveStream)
    {
        return $this->liveStreamService->show($liveStream);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LiveStream $liveStream)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLiveStreamRequest $request, LiveStream $liveStream)
    {
        return $this->liveStreamService->update($liveStream, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveStream $liveStream)
    {
        return $this->liveStreamService->destroy($liveStream);
    }
}
  