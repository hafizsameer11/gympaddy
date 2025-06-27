<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Notifications\PushMessageNotification;

class ChatService
{
    public function sendMessage($sender, $receiver, $message)
    {
        // Store the message in the database
        $chatMessage = ChatMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $message,
        ]);

        // Send push notification if receiver has device_token
        if ($receiver->device_token) {
            $receiver->notify(new PushMessageNotification(
                'New Message',
                "{$sender->fullname} sent you a message: {$message}"
            ));
        }

        return $chatMessage;
    }

    // ...existing methods...
}