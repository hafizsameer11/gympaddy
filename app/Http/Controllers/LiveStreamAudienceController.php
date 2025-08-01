<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamAudience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveStreamAudienceController extends Controller
{
    public function join(Request $request, $liveStreamId)
    {
        $userId = auth()->id();

        $audience = LiveStreamAudience::updateOrCreate(
            ['live_stream_id' => $liveStreamId, 'user_id' => $userId],
            ['joined_at' => now(), 'left_at' => null]
        );

        return response()->json([
            'status' => true,
            'message' => 'User joined the stream',
            'data' => $audience
        ]);
    }

    // ✅ Mark user as left
    public function leave(Request $request, $liveStreamId)
    {
        $userId = auth()->id();

        $audience = LiveStreamAudience::where('live_stream_id', $liveStreamId)
            ->where('user_id', $userId)
            ->first();

        if ($audience) {
            $audience->update(['left_at' => now()]);
        }

        return response()->json([
            'status' => true,
            'message' => 'User left the stream'
        ]);
    }

    // ✅ Optional: Get current audience
    public function currentAudience($liveStreamId)
    {
        $audience = LiveStreamAudience::with('user')
            ->where('live_stream_id', $liveStreamId)
            ->whereNull('left_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $audience
        ]);
    }
    public function currentAudienceCount($id){
        $audience = LiveStreamAudience::where('live_stream_id', $id)
        ->whereNull('left_at')
        ->count();
        return response()->json([
            'status' => true,
            'data' => $audience,
            'count'=>$audience
        ]);

    }
    public function endLive($channel_name)
    {
        $liveStream = LiveStream::where('agora_channel', $channel_name)->first();
        if ($liveStream) {
            $liveStream->update(['is_active' => false]);
        }
        Log::info('live streaiming edning',[$liveStream]);
        return response()->json([
            'status' => true,
            'message' => 'Live stream ended'
        ]);
    }
}
