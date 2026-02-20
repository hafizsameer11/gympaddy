<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getAllNotifications(Request $request)
    {
        try {
            $query = Notification::query();

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $notifications = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'currentPage' => $notifications->currentPage(),
                        'totalPages' => $notifications->lastPage(),
                        'totalItems' => $notifications->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getNotificationById($id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Notification not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $notification]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'message' => 'required|string',
                'type' => 'required|string',
                'targetUsers' => 'array',
            ]);

            $targetUsers = $validated['targetUsers'] ?? [];
            
            foreach ($targetUsers as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'status' => 'sent',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => [
                    'id' => 'notif_' . time(),
                    'recipients' => count($targetUsers)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function sendBulkNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'message' => 'required|string',
                'type' => 'required|string',
                'userType' => 'required|string',
            ]);

            $query = User::query();
            
            if ($validated['userType'] === 'subscribers') {
                $query->where('subscription_status', 'active');
            } elseif ($validated['userType'] === 'active') {
                $query->where('status', 'online');
            }

            $users = $query->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'status' => 'sent',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk notification sent successfully',
                'data' => [
                    'id' => 'notif_' . time(),
                    'recipients' => $users->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Notification not found']], 404);
            }
            $notification->update(['is_read' => true]);
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
