<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Like::all();
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
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|string|in:Post,Reel',
        ]);
        if ($validator->fails()) {
            \Log::warning('Like creation validation failed', ['errors' => $validator->errors()]);
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

        // Check if already liked
        $alreadyLiked = \App\Models\Like::where([
            'user_id' => $data['user_id'],
            'likeable_id' => $data['likeable_id'],
            'likeable_type' => $data['likeable_type'],
        ])->exists();

        if ($alreadyLiked) {
            return response()->json([
                'status' => 'error',
                'code' => 409,
                'message' => 'Already liked',
                'errors' => [[
                    'field' => 'like',
                    'reason' => 'User has already liked this item',
                    'suggestion' => 'You cannot like the same item more than once'
                ]],
            ], 409);
        }

        $like = \App\Models\Like::create($data);
        return response()->json($like, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Like $like)
    {
        return $like;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Like $like)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Like $like)
    {
        $validator = \Validator::make($request->all(), [
            // ...fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('Like update validation failed', ['errors' => $validator->errors()]);
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
        $like->update($validator->validated());
        return response()->json($like);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Like $like)
    {
        $like->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
