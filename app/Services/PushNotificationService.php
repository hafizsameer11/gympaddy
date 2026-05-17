<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotificationService
    ) {
    }

    /**
     * Save an in-app notification and optionally send a device push.
     */
    public function notifyUser(
        int $userId,
        string $title,
        string $body,
        string $type = 'general',
        array $data = [],
        bool $attemptPush = true
    ): array {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'status' => 'sent',
            'is_read' => false,
        ]);

        $push = ['status' => 'skipped', 'message' => 'Push not attempted'];

        if ($attemptPush) {
            $push = $this->sendPushToUser($userId, $title, $body, array_merge($data, [
                'type' => $type,
                'notification_id' => (string) $notification->id,
            ]));
        }

        return [
            'status' => 'success',
            'notification_id' => $notification->id,
            'notification' => $notification,
            'push' => $push,
        ];
    }

    /**
     * @deprecated Use notifyUser() — kept for existing callers.
     */
    public function sendToUserById(int $userId, string $title, string $body, array $data = []): array
    {
        $type = isset($data['type']) && is_string($data['type']) ? $data['type'] : 'general';

        return $this->notifyUser($userId, $title, $body, $type, $data);
    }

    protected function sendPushToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $user = User::find($userId);

        if (!$user) {
            Log::warning("Push skipped: user not found ($userId)");

            return ['status' => 'error', 'message' => 'User not found'];
        }

        $fcmToken = $this->resolveFcmToken($user);

        if (!$fcmToken) {
            Log::info("Push skipped: no FCM token for user $userId (in-app notification still saved)");

            return ['status' => 'skipped', 'message' => 'No FCM token on device yet'];
        }

        try {
            $response = $this->firebaseNotificationService->sendNotification(
                $fcmToken,
                $title,
                $body,
                (string) $userId,
                $data
            );

            Log::info("Push sent to user $userId", ['response' => $response]);

            return ['status' => 'success', 'message' => 'Push sent', 'response' => $response];
        } catch (\Throwable $e) {
            Log::error("Push failed for user $userId: " . $e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function resolveFcmToken(User $user): ?string
    {
        $token = $user->fcmToken ?? $user->device_token ?? null;

        if (!is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token !== '' ? $token : null;
    }
}
