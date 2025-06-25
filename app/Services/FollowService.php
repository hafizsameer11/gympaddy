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

    public function destroy(Follow $follow)
    {
        $follow->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
