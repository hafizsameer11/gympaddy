<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();
        return Post::with(['user', 'comments', 'likes'])
            ->withCount('allComments')
            ->where('user_id', $user->id)
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'media_url' => 'nullable|url',
        ]);
        $post = Post::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
        ]);
        return response()->json($post->load(['user', 'comments', 'likes']), 201);
    }

    public function show(Post $post)
    {
        $user = Auth::user();
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return $post->load(['user', 'comments', 'likes'])->loadCount('allComments');
    }

    public function update(Request $request, Post $post)
    {
        $user = Auth::user();
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'media_url' => 'nullable|url',
        ]);
        $post->update($validated);
        return response()->json($post->load(['user', 'comments', 'likes']));
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
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
