<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Http\Requests\StoreFollowRequest;
use App\Http\Requests\UpdateFollowRequest;
use App\Services\FollowService;

class FollowController extends Controller
{
    protected FollowService $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->followService->index();
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
    public function store(StoreFollowRequest $request)
    {
        return $this->followService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Follow $follow)
    {
        return $this->followService->show($follow);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Follow $follow)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFollowRequest $request, Follow $follow)
    {
        return $this->followService->update($follow, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userId)
    {
        return $this->followService->destroy($userId);
    }
    public function getFollowers($userId)
    {
        return $this->followService->getFollowersWithFollowBack($userId);
    }
    public function getFollowing($userId){
        return $this->followService->getFollowing($userId);
    }
}
