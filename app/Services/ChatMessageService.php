<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\DB;

class ChatMessageService
{
    public function index($params = [])
    {
        $user = auth()->user();

        // Filter by conversation_id
        if (isset($params['conversation_id'])) {
            $conversation = Conversation::find($params['conversation_id']);
            if (!$conversation || ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id)) {
                return response()->json(['data' => []]);
            }

            $messages = ChatMessage::with(['sender:id,username,fullname,profile_picture', 'receiver:id,username,fullname,profile_picture'])
                ->where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) use ($user) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'sender' => $message->sender,
                        'receiver' => $message->receiver,
                        'direction' => $message->sender_id === $user->id ? 'sent' : 'received',
                        'created_at' => $message->created_at,
                    ];
                });

            return response()->json([
                'conversation_id' => $conversation->id,
                'messages' => $messages,
            ]);
        }

        // Optionally filter by receiver_id (for 1-on-1 chat)
        $receiverId = $params['receiver_id'] ?? null;

        if ($receiverId) {
            $conversation = $this->findConversation($user->id, $receiverId);
            if (!$conversation) {
                return response()->json(['data' => []]);
            }

            $messages = ChatMessage::with(['sender:id,username,fullname,profile_picture', 'receiver:id,username,fullname,profile_picture'])
                ->where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) use ($user) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'sender' => $message->sender,
                        'receiver' => $message->receiver,
                        'direction' => $message->sender_id === $user->id ? 'sent' : 'received',
                        'created_at' => $message->created_at,
                    ];
                });

            return response()->json([
                'conversation_id' => $conversation->id,
                'messages' => $messages,
            ]);
        }

        // Otherwise, return all conversations for the user with last message
        $conversations = Conversation::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with([
                'user1:id,username,fullname,profile_picture',
                'user2:id,username,fullname,profile_picture',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->get();

        return response()->json($conversations);
    }


    public function store($validated)
    {
        $senderId = $validated['sender_id'];
        $receiverId = $validated['receiver_id'];

        // Support marketplace listing chat (optional: pass listing_id in $validated)
        if (isset($validated['listing_id'])) {
            $listing = MarketplaceListing::find($validated['listing_id']);
            if ($listing) {
                $receiverId = $listing->user_id;
            }
        }

        // Always order user1_id < user2_id for uniqueness
        [$user1, $user2] = $senderId < $receiverId
            ? [$senderId, $receiverId]
            : [$receiverId, $senderId];

        // Find or create conversation (bi-directional)
        $conversation = $this->findConversation($user1, $user2);
        if (!$conversation) {
            $conversation = Conversation::create([
                'user1_id' => $user1,
                'user2_id' => $user2,
            ]);
        }

        // Create the message
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $validated['message'],
        ]);

        $message->load(['sender', 'receiver', 'conversation']);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'Message sent',
            'data' => $message
        ], 201);
    }

    public function show(ChatMessage $chatMessage)
    {
        $chatMessage->load(['sender', 'receiver', 'conversation']);
        return $chatMessage;
    }

    public function update(ChatMessage $chatMessage, $validated)
    {
        $chatMessage->update($validated);
        $chatMessage->load(['sender', 'receiver', 'conversation']);
        return response()->json($chatMessage);
    }

    public function destroy(ChatMessage $chatMessage)
    {
        $chatMessage->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function listConversations()
    {
        $user = auth()->user();
        $conversations = Conversation::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with([
                'user1:id,username,fullname,profile_picture',
                'user2:id,username,fullname,profile_picture',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->get()
            ->map(function ($conv) use ($user) {
                $other = $conv->user1_id === $user->id ? $conv->user2 : $conv->user1;
                return [
                    'conversation_id' => $conv->id,
                    'other_user' => [
                        'id' => $other->id,
                        'username' => $other->username,
                        'fullname' => $other->fullname,
                        'profile_picture_url' => $other->profile_picture ? asset('storage/' . $other->profile_picture) : null,
                    ],
                    'last_message' => $conv->messages->first(),
                    'type' => $conv->type, // Include type if needed
                    'created_at' => $conv->created_at,
                    'updated_at' => $conv->updated_at,
                    
                ];
            });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'conversations' => $conversations,
        ]);
    }

    private function findConversation($user1, $user2)
    {
        return Conversation::where(function ($q) use ($user1, $user2) {
            $q->where('user1_id', $user1)->where('user2_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('user1_id', $user2)->where('user2_id', $user1);
        })->first();
    }
}
