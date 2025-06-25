<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Log;

class PostService
{
    public function index($user)
    {
        return Post::with(['user', 'comments', 'likes'])
            ->withCount('allComments')
            ->where('user_id', $user->id)
            ->paginate(20);
    }

    public function store($user, $validated)
    {
        
        $post = Post::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
        ]);
        return response()->json($post->load(['user', 'comments', 'likes']), 201);
    }

    public function show($user, Post $post)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return $post->load(['user', 'comments', 'likes'])->loadCount('allComments');
    }

    public function update($user, Post $post, $validated)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $post->update($validated);
        return response()->json($post->load(['user', 'comments', 'likes']));
    }

    public function destroy($user, Post $post)
    {
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        try {
            $post->delete();
            return response()->json(['message' => 'Post deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete post.', 'error' => $e->getMessage()], 500);
        }
    }
}
