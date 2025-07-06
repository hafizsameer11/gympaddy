<?php

namespace App\Http\Controllers;

use App\Models\LiveStreamGift;
use Illuminate\Http\Request;

class LiveStreamGiftController extends Controller
{
     public function index($liveStreamId)
    {
        $gifts = LiveStreamGift::with('sender')
            ->where('live_stream_id', $liveStreamId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $gifts
        ]);
    }

    // ✅ Send a gift
    public function store(Request $request, $liveStreamId)
    {
        $request->validate([
            'gift_name' => 'required|string|max:255',
            'gift_icon' => 'nullable|string',
            'gift_value' => 'required|integer|min:1',
        ]);

        $gift = LiveStreamGift::create([
            'live_stream_id' => $liveStreamId,
            'sender_id' => auth()->id(),
            'gift_name' => $request->gift_name,
            'gift_icon' => $request->gift_icon,
            'gift_value' => $request->gift_value,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Gift sent successfully',
            'data' => $gift->load('sender')
        ]);
    }
}
