<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->notificationService->index();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request)
    {
        return $this->notificationService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        return $this->notificationService->show($notification);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationRequest $request, Notification $notification)
    {
        return $this->notificationService->update($notification, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        return $this->notificationService->destroy($notification);
    }

    public function unread()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->where(function ($query) {
                $query->where('is_read', false)->orWhereNull('is_read');
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $notifications->count(),
            'data' => $notifications,
        ]);
    }

    public function markRead(Notification $notification)
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $updatePayload = ['is_read' => true];
        if (Schema::hasColumn('notifications', 'read_at')) {
            $updatePayload['read_at'] = now();
        }

        $notification->update($updatePayload);
        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }
}
