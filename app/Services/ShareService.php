<?php

namespace App\Services;

use App\Models\Share;

class ShareService
{
    public function index()
    {
        return Share::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $share = Share::create($data);
        return response()->json($share, 201);
    }

    public function show(Share $share)
    {
        return $share;
    }

    public function update(Share $share, $validated)
    {
        $share->update($validated);
        return response()->json($share);
    }

    public function destroy(Share $share)
    {
        $share->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
