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

        // ✅ Require conversation_id
        if (!isset($params['conversation_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'conversation_id is required',
                'data' => [],
            ], 400);
        }

        // ✅ Fetch and validate conversation
        $conversation = Conversation::find($params['conversation_id']);

        if (
            !$conversation ||
            ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id)
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conversation not found or access denied',
                'data' => [],
            ], 403);
        }

        // ✅ Fetch messages
        $messages = ChatMessage::with([
            'sender:id,username,fullname,profile_picture',
            'receiver:id,username,fullname,profile_picture'
        ])
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
                    'image'=>$message->image
                ];
            });

        return response()->json([
            'status' => 'success',
            'conversation_id' => $conversation->id,
            'type' => $conversation->type,
            'messages' => $messages,
        ]);
    }



    public function store($validated)
    {
        $senderId = $validated['sender_id'];
        $receiverId = $validated['receiver_id'];

        $conversation = null;

        // ✅ 1. If conversation_id is passed, validate and use it
        if (isset($validated['conversation_id'])) {
            $conversation = Conversation::find($validated['conversation_id']);

            if (
                !$conversation ||
                ($conversation->user1_id !== $senderId && $conversation->user2_id !== $senderId)
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unauthorized conversation_id',
                    'code' => 403,
                ], 403);
            }
        } else {
            // ✅ 2. Optional marketplace override
            if (isset($validated['listing_id'])) {
                $listing = MarketplaceListing::find($validated['listing_id']);
                if ($listing) {
                    $receiverId = $listing->user_id;
                }
            }

            // ✅ 3. Try to find a 'social' conversation between the users
            $conversation = Conversation::where('user1_id', $senderId)
                ->where('user2_id', $receiverId)
                ->where('type', 'social')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user1_id' => $senderId,
                    'user2_id' => $receiverId,
                    'type' => 'social',
                ]);
            }
        }
        //check if has image
        
        // ✅ 4. Create the message
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $validated['message'] ?? 'Sent an image',
            'image'=>$validated['imagePath']
        ]);
   

        $message->load(['sender', 'receiver', 'conversation']);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'Message sent',
            'data' => $message,
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

    $conversations = Conversation::where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
        })
        ->with([
            'user1:id,username,fullname,profile_picture',
            'user2:id,username,fullname,profile_picture',
            'messages' => function ($q) {
                $q->select('id','conversation_id','message','sender_id','receiver_id','read','created_at')
                  ->latest()
                  ->limit(1);
            },
        ])
        ->withMax('messages', 'created_at')
        ->orderByRaw('messages_max_created_at IS NULL')
        ->orderByDesc('messages_max_created_at')
        ->orderByDesc('updated_at')
        ->get()
        ->map(function ($conv) use ($user) {
            $otherUser = $conv->user1_id === $user->id ? $conv->user2 : $conv->user1;

            // count unread messages for this user in this conversation
            $unreadCount = ChatMessage::where('conversation_id', $conv->id)
                ->where('receiver_id', $user->id)
                ->where('read', false) // assuming read is boolean
                ->count();

            return [
                'conversation_id' => $conv->id,
                'type'            => $conv->type,
                'other_user'      => [
                    'id'                  => $otherUser->id,
                    'username'            => $otherUser->username,
                    'fullname'            => $otherUser->fullname,
                    'profile_picture_url' => $otherUser->profile_picture
                        ? asset('storage/' . ltrim($otherUser->profile_picture, '/'))
                        : null,
                ],
                'last_message'    => $conv->messages->first() ? [
                    'id'            => $conv->messages->first()->id,
                    'message'       => $conv->messages->first()->message,
                    'sender_id'     => $conv->messages->first()->sender_id,
                    'receiver_id'   => $conv->messages->first()->receiver_id,
                    'created_at'    => $conv->messages->first()->created_at,
                    'unread_count'  => $unreadCount,
                ] : [
                    'unread_count'  => $unreadCount
                ],
                'created_at'      => $conv->created_at,
                'updated_at'      => $conv->updated_at,
                'last_message_at' => $conv->messages_max_created_at,
            ];
        });

    return response()->json([
        'status'        => 'success',
        'code'          => 200,
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
