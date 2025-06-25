<?php

namespace App\Services;

use App\Models\Reel;

class ReelService
{
    public function index()
    {
        return Reel::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $reel = Reel::create($data);
        return response()->json($reel, 201);
    }

    public function show(Reel $reel)
    {
        return $reel;
    }

    public function update(Reel $reel, $validated)
    {
        $reel->update($validated);
        return response()->json($reel);
    }

    public function destroy(Reel $reel)
    {
        $reel->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
