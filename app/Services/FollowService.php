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

        $follow = Follow::where('follower_id', auth()->id())
            ->where('followed_id', $userId)
            ->first();
            //check if follow relationship exists dekete otherwise create a new one
        if (!$follow) {
            //create a new follow relationship
            $follow = Follow::create([
                'follower_id' => auth()->id(),
                'followed_id' => $userId,
            ]);
            // return response()->json(['message' => 'You are not following this user'], 404);
        }else{

            $follow->delete() ;
        }
        return response()->json(['message' => 'Deleted', 'status' => 'success'], 200);
    }
   public function getFollowersWithFollowBack($userId)
{
    $followers = Follow::where('followed_id', $userId)
        ->with('follower')
        ->get();

    // Get IDs of all followers
    $followerIds = $followers->pluck('follower.id')->toArray();

    // Get who $userId is following (to check for follow-back)
    $followBacks = Follow::where('follower_id', $userId)
        ->whereIn('followed_id', $followerIds)
        ->pluck('followed_id')
        ->toArray();

    // Map results with `is_following_back`
    $followers = $followers->map(function ($item) use ($followBacks) {
        $item->follower->is_following_back = in_array($item->follower->id, $followBacks);
        return $item;
    });

    return $followers;
}

    public function getFollowing($userId)
    {
        return Follow::where('follower_id', $userId)->with('followed')->get();
    }
}
