<?php

namespace App\Http\Controllers;

use App\Models\LiveStreamChat;
use Illuminate\Http\Request;

class LiveStreamChatController extends Controller
{
      // ✅ Fetch chat messages for a specific live stream
    public function index($id)
    {
        $chats = LiveStreamChat::with('user')
            ->where('live_stream_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $chats
        ]);
    }

    // ✅ Store a new chat message for a live stream
    public function store(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chat = LiveStreamChat::create([
            'user_id' => auth()->id(),
            'live_stream_id' => $id,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $chat->load('user')
        ]);
    }
}
