<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class CommentService
{
    public function __construct(
        protected PushNotificationService $pushNotificationService
    ) {
    }
    public function index(Request $request)
    {
        $postId = $request->query('post_id');
        if (!$postId) {
            return response()->json(['message' => 'post_id is required'], 400);
        }
        $comments = Comment::with(['user', 'replies.user', 'replies.replies'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $comment = Comment::create($data);
        $comment->load(['user', 'replies']);

        $post = Post::find($data['post_id'] ?? null);
        if ($post && (int) $post->user_id !== (int) $user->id) {
            $commenter = $comment->user ?? User::find($user->id);
            $preview = $data['content'] ?? '';
            if (strlen($preview) > 120) {
                $preview = substr($preview, 0, 117) . '...';
            }
            $this->pushNotificationService->notifyUser(
                (int) $post->user_id,
                'New comment on your post',
                ($commenter?->username ?? 'Someone') . ': ' . ($preview ?: 'left a comment'),
                'comment',
                ['post_id' => (string) $post->id, 'comment_id' => (string) $comment->id]
            );
        }

        return response()->json($comment, 201);
    }

    public function show(Comment $comment)
    {
        return $comment->load(['user', 'replies.user', 'replies.replies']);
    }

    public function update(Comment $comment, $validated)
    {
        $comment->update($validated);
        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
