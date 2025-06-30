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

    public function destroy($userId)
    {
        if (auth()->id() == $userId) {
            return response()->json(['message' => 'You cannot follow or unfollow yourself'], 400);
        }

        $follow = Follow::where('follower_id', auth()->id())
            ->where('followed_id', $userId)
            ->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['message' => 'Unfollowed', 'status' => 'success'], 200);
        } else {
            $newFollow = Follow::create([
                'follower_id' => auth()->id(),
                'followed_id' => $userId,
            ]);
            return response()->json(['message' => 'Followed', 'status' => 'success', 'data' => $newFollow], 201);
        }
    }
    public function getFollowers($userId)
    {
        return Follow::where('followed_id', $userId)->with('follower')->get();
    }
    public function getFollowing($userId)
    {
        return Follow::where('follower_id', $userId)->with('followed')->get();
    }
}
