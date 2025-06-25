<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Services\ShareService;

class ShareController extends Controller
{
    protected ShareService $shareService;

    public function __construct(ShareService $shareService)
    {
        $this->shareService = $shareService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->shareService->index();
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
    public function store(StoreShareRequest $request)
    {
        return $this->shareService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        return $this->shareService->show($share);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShareRequest $request, Share $share)
    {
        return $this->shareService->update($share, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Share $share)
    {
        return $this->shareService->destroy($share);
    }
}
 