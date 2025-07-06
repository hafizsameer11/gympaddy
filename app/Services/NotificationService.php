<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function index()
    {
        $user=Auth::user();
                return Notification::where('user_id',$user->id)->latest()->get();
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
