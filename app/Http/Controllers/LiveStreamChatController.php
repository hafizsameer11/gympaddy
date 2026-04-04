<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveStreamChatController extends Controller
{
    // ✅ Fetch chat messages for a specific live stream
  public function index($id)
{
    $chats = LiveStreamChat::with(['user', 'replyTo.user']) // eager load nested reply user
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
            'type' => 'nullable|string',
            'reply_to_id' => 'nullable|exists:live_stream_chats,id',
        ]);

        $stream = LiveStream::find($id);
        if (!$stream || !$stream->is_active || ($stream->status ?? '') === 'ended') {
            return response()->json([
                'status' => false,
                'message' => 'This live stream has ended. Chat is closed.',
            ], 410);
        }

        Log::info("Request object for messaging on live stream", $request->all());

        $chat = LiveStreamChat::create([
            'user_id' => auth()->id(),
            'live_stream_id' => $id,
            'message' => $request->message,
            'type' => $request->type ?? 'message',
            'reply_to_id' => $request->reply_to_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $chat->load(['user', 'replyTo.user']),
        ]);
    }
}
