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
        $validator = \Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);
        if ($validator->fails()) {
            \Log::warning('Comment creation validation failed', ['errors' => $validator->errors()]);
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
        $data = $validator->validated();
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
        $validator = \Validator::make($request->all(), [
            'content' => 'sometimes|required|string',
        ]);
        if ($validator->fails()) {
            \Log::warning('Comment update validation failed', ['errors' => $validator->errors()]);
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
        $comment->update($validator->validated());
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
