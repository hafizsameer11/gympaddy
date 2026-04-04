<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamAudience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveStreamAudienceController extends Controller
{
    /**
     * Mark stream ended: inactive, status ended, and all "present" audience rows get left_at.
     */
    public static function finalizeStreamEnd(LiveStream $liveStream): void
    {
        $liveStream->update([
            'is_active' => false,
            'status' => 'ended',
        ]);
        LiveStreamAudience::where('live_stream_id', $liveStream->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);
    }

    public function join(Request $request, $liveStreamId)
    {
        $stream = LiveStream::find($liveStreamId);
        if (!$stream || !$stream->is_active || ($stream->status ?? '') === 'ended') {
            return response()->json([
                'status' => false,
                'message' => 'This live stream has ended or is not available.',
            ], 410);
        }

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

    /**
     * Authenticated host ends their own stream (preferred for mobile app).
     */
    public function endHostStream($id)
    {
        $stream = LiveStream::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$stream) {
            return response()->json([
                'status' => false,
                'message' => 'Stream not found or you are not the host.',
            ], 404);
        }

        self::finalizeStreamEnd($stream);

        return response()->json([
            'status' => true,
            'message' => 'Live stream ended',
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
    public function heartbeat($id)
    {
        $stream = LiveStream::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if ($stream) {
            $stream->update(['last_heartbeat_at' => now()]);
        }

        return response()->json(['status' => true]);
    }

    public function endLive($channel_name)
    {
        $liveStream = LiveStream::where('agora_channel', $channel_name)->first();
        if ($liveStream) {
            self::finalizeStreamEnd($liveStream);
        }
        Log::info('live streaming ending', ['channel' => $channel_name, 'stream_id' => $liveStream?->id]);
        return response()->json([
            'status' => true,
            'message' => 'Live stream ended'
        ]);
    }
}
