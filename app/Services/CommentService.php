<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentService
{
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
        return response()->json($comment->load(['user', 'replies']), 201);
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
