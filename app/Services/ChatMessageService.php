<?php

namespace App\Services;

use App\Models\ChatMessage;
use Illuminate\Database\QueryException;

class ChatMessageService
{
    public function index()
    {
        return ChatMessage::all();
    }

    public function store($validated)
    {
        try {
            $chatMessage = ChatMessage::create($validated);
            return response()->json($chatMessage, 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found or invalid foreign key.',
                    'code' => 400
                ], 400);
            }
            throw $e;
        }
    }

    public function show(ChatMessage $chatMessage)
    {
        return $chatMessage;
    }

    public function update(ChatMessage $chatMessage, $validated)
    {
        $chatMessage->update($validated);
        return response()->json($chatMessage);
    }

    public function destroy(ChatMessage $chatMessage)
    {
        $chatMessage->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
