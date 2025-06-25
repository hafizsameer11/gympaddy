<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function index()
    {
        return User::paginate(20);
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function store($validated)
    {
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function update($id, $validated)
    {
        $user = User::findOrFail($id);
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
