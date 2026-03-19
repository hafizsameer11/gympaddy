<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function formatNotification(Notification $n): array
    {
        $user = $n->user;
        return [
            'id'         => (string) $n->id,
            'title'      => $n->title ?? '',
            'message'    => $n->body ?? '',
            'type'       => $n->type ?? 'broadcast',
            'status'     => $n->status ?? 'sent',
            'is_read'    => (bool) $n->is_read,
            'created_at' => $n->created_at?->toIso8601String() ?? '',
            'time'       => $n->created_at?->diffForHumans() ?? '',
            'timestamp'  => $n->created_at?->format('Y-m-d h:i A') ?? '',
            'user'       => $user ? [
                'id'              => $user->id,
                'username'        => $user->username ?? 'Unknown',
                'profile_picture' => $user->profile_picture ?? null,
            ] : [
                'id'              => null,
                'username'        => 'System',
                'profile_picture' => null,
            ],
        ];
    }

    public function getAllNotifications(Request $request)
    {
        try {
            $query = Notification::with('user:id,username,profile_picture');

            if ($request->boolean('broadcast')) {
                $query->whereNull('user_id')->where('type', 'broadcast');
            } else {
                if ($request->has('type') && $request->type !== 'all') {
                    $query->where('type', $request->type);
                }
                if ($request->has('status') && $request->status !== 'all') {
                    $query->where('status', $request->status);
                }
            }

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $notifications = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => collect($notifications->items())->map(fn($n) => $this->formatNotification($n)),
                    'pagination' => [
                        'currentPage' => $notifications->currentPage(),
                        'totalPages'  => $notifications->lastPage(),
                        'totalItems'  => $notifications->total(),
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
            return response()->json(['success' => true, 'data' => $this->formatNotification($notification)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string',
                'message'     => 'required|string',
                'type'        => 'nullable|string',
                'targetUsers' => 'array',
            ]);

            $type        = $validated['type'] ?? 'broadcast';
            $targetUsers = $validated['targetUsers'] ?? [];

            // Store one broadcast record for history
            $notification = Notification::create([
                'user_id' => null,
                'title'   => $validated['title'],
                'body'    => $validated['message'],
                'type'    => $type,
                'status'  => 'sent',
            ]);

            // Also send to specific target users if provided
            foreach ($targetUsers as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title'   => $validated['title'],
                    'body'    => $validated['message'],
                    'type'    => $type,
                    'status'  => 'sent',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data'    => $this->formatNotification($notification),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function sendBulkNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'    => 'required|string',
                'message'  => 'required|string',
                'type'     => 'nullable|string',
                'userType' => 'required|string',
            ]);

            $type = $validated['type'] ?? 'broadcast';

            $notification = Notification::create([
                'user_id' => null,
                'title'   => $validated['title'],
                'body'    => $validated['message'],
                'type'    => $type,
                'status'  => 'sent',
            ]);

            $userQuery = User::query();
            if ($validated['userType'] === 'active') {
                $userQuery->where('status', 'online');
            }

            $userIds = $userQuery->pluck('id');

            $records = $userIds->map(fn($uid) => [
                'user_id'    => $uid,
                'title'      => $validated['title'],
                'body'       => $validated['message'],
                'type'       => $type,
                'status'     => 'sent',
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            foreach (array_chunk($records, 500) as $chunk) {
                DB::table('notifications')->insert($chunk);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to ' . count($records) . ' users',
                'data'    => $this->formatNotification($notification),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateNotificationStatus(Request $request, $id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Notification not found']], 404);
            }

            $validated = $request->validate([
                'status' => 'required|string|in:sent,reviewed,approved,rejected',
            ]);

            $notification->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Notification status updated',
                'data'    => $this->formatNotification($notification),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Notification not found']], 404);
            }
            $notification->delete();
            return response()->json(['success' => true, 'message' => 'Notification deleted successfully']);
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

    // For admin dashboard badge counter (e.g., unread support messages).
    public function getUnreadCount(Request $request)
    {
        try {
            $admin = Auth::user();
            if (!$admin) {
                return response()->json(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Admin not authenticated']], 401);
            }

            $type = $request->query('type', 'all');

            $query = Notification::where('user_id', $admin->id)
                ->where('is_read', false);

            if ($type !== 'all' && $type !== null && $type !== '') {
                $query->where('type', $type);
            }

            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
