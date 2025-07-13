<?php

namespace App\Services;

use App\Models\LiveStream;

class LiveStreamService
{
    public function index()
    {
        return LiveStream::with('user')->where('is_active',1)->latest()->get();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $liveStream = LiveStream::create($data);
        return response()->json($liveStream, 201);
    }

    public function show(LiveStream $liveStream)
    {
        return $liveStream;
    }

    public function update(LiveStream $liveStream, $validated)
    {
        $liveStream->update($validated);
        return response()->json($liveStream);
    }

    public function destroy(LiveStream $liveStream)
    {
        $liveStream->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
