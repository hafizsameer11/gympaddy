<?php

namespace App\Http\Controllers;

use App\Models\DailyCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DailyCallController extends Controller
{
    public function startCall(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|integer',
            'type' => 'required|in:voice,video',
            'channel_name' => 'required|string|unique:daily_calls,channel_name',
        ]);

        $response = Http::withToken(env('DAILY_API_KEY','cf73c3f73ef9cfdcd4e250bd2e461c51222610422eaaf089cefc2fc27d873e4f'))
            ->post('https://api.daily.co/v1/rooms', [
                'name' => $validated['channel_name'],
                'properties' => [
                    'audio' => true,
                    'video' => $validated['type'] === 'video',
                    'start_video_off' => $validated['type'] === 'voice',
                    'start_audio_off' => false,
                ]
            ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to create Daily room'], 500);
        }

        $roomUrl = $response->json('url');

        $call = DailyCall::create([
            'caller_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'channel_name' => $validated['channel_n ame'],
            'room_url' => $roomUrl,
            'type' => $validated['type'],
            'status' => 'initiated',
            'response' => $response->json(),
        ]);

        return response()->json(['call' => $call]);
    }

    public function incomingCall(Request $request)
    {
        $call = DailyCall::where('receiver_id', auth()->id())
            ->where('status', 'initiated')
            ->latest()
            ->first();

        return response()->json(['call' => $call]);
    }

    public function endCall(Request $request)
    {
        $request->validate(['channel_name' => 'required|string']);

        $call = DailyCall::where('channel_name', $request->channel_name)
            ->where(function ($q) {
                $q->where('caller_id', auth()->id())
                  ->orWhere('receiver_id', auth()->id());
            })
            ->firstOrFail();

        $call->update(['status' => 'ended']);

        return response()->json(['message' => 'Call ended']);
    }
}
