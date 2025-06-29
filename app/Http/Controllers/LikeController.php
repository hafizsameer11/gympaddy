<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Http\Requests\StoreLikeRequest;
use App\Http\Requests\UpdateLikeRequest;
use App\Services\LikeService;

class LikeController extends Controller
{
    protected LikeService $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->likeService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLikeRequest $request)
    {
        return $this->likeService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Like $like)
    {
        return $this->likeService->show($like);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLikeRequest $request, Like $like)
    {
        return $this->likeService->update($like, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Like $like)
    {
        return $this->likeService->destroy($like);
    }
    public function likePost($postId)
    {
        $user = auth()->user();
        $payload = ['likeable_id ' => $postId, 'user_id' => $user->id];
        return $this->likeService->likePost($payload);
    }
}
