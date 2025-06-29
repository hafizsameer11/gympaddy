<?php

namespace App\Services;

use App\Models\Follow;

class FollowService
{
    public function index()
    {
        return Follow::all();
    }

    public function store($validated)
    {
        $data = $validated;
        if ($data['follower_id'] == $data['followed_id']) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }
        //check if the follow relationship already exists
        if (Follow::where('follower_id', $data['follower_id'])
            ->where('followed_id', $data['followed_id'])
            ->exists()
        ) {
            return response()->json(['message' => 'You are already following this user'], 409);
        }
        $follow = Follow::create($validated);
        return response()->json($follow, 201);
    }

    public function show(Follow $follow)
    {
        return $follow;
    }

    public function update(Follow $follow, $validated)
    {
        $follow->update($validated);
        return response()->json($follow);
    }

    public function destroy($id)
    {
        //get the follow relationship by id
        $follow = Follow::findOrFail($id);
        $follow->delete();
        return response()->json(['message' => 'Deleted', 'status' => 'success'], 200);
    }
    public function getFollowers($userId)
    {
        return Follow::where('followed_id', $userId)->with('follower')->get();
    }
}
