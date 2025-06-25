<?php

namespace App\Services;

use App\Models\Like;

class LikeService
{
    public function index()
    {
        return Like::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;

        $alreadyLiked = Like::where([
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

        $like = Like::create($data);
        return response()->json($like, 201);
    }

    public function show(Like $like)
    {
        return $like;
    }

    public function update(Like $like, $validated)
    {
        $like->update($validated);
        return response()->json($like);
    }

    public function destroy(Like $like)
    {
        $like->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
