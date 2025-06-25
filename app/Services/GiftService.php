<?php

namespace App\Services;

use App\Models\Gift;

class GiftService
{
    public function index()
    {
        return Gift::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['from_user_id'] = $user->id;
        $data['amount'] = $data['value'];
        $gift = Gift::create($data);
        return response()->json($gift, 201);
    }

    public function show(Gift $gift)
    {
        return $gift;
    }

    public function update(Gift $gift, $validated)
    {
        $gift->update($validated);
        return response()->json($gift);
    }

    public function destroy(Gift $gift)
    {
        $gift->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
