<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $postId = $request->query('post_id');
        if (!$postId) {
            return response()->json(['message' => 'post_id is required'], 400);
        }
        // Get top-level comments for the post, with nested replies
        $comments = \App\Models\Comment::with(['user', 'replies.user', 'replies.replies'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);
        $data['user_id'] = $request->user()->id;
        $comment = \App\Models\Comment::create($data);
        return response()->json($comment->load(['user', 'replies']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Comment $comment)
    {
        return $comment->load(['user', 'replies.user', 'replies.replies']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $data = $request->validate([
            'content' => 'sometimes|string',
        ]);
        $comment->update($data);
        return response()->json($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
