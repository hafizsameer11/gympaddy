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
        $user = \Auth::user();
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'media_url' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            \Log::warning('Post creation validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $validated = $validator->validated();
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
        $user = \Auth::user();
        if ($post->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'media_url' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            \Log::warning('Post update validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $post->update($validator->validated());
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
