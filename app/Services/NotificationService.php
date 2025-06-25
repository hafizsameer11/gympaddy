<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function index()
    {
        return Notification::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $notification = Notification::create($data);
        return response()->json($notification, 201);
    }

    public function show(Notification $notification)
    {
        return $notification;
    }

    public function update(Notification $notification, $validated)
    {
        $notification->update($validated);
        return response()->json($notification);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
