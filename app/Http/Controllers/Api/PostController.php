<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->middleware('auth:sanctum');
        $this->postService = $postService;
    }

    public function index()
    {
        $user = Auth::user();
        return $this->postService->index($user);
    }

    public function store(StorePostRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        
        // Add media files to validated data
        if ($request->hasFile('media')) {
            $validated['media'] = $request->file('media');
        }
        
        return $this->postService->store($user, $validated);
    }

    public function show(Post $post)
    {
        $user = Auth::user();
        return $this->postService->show($user, $post);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $user = Auth::user();
        return $this->postService->update($user, $post, $request->validated());
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
        return $this->postService->destroy($user, $post);
    }
}
