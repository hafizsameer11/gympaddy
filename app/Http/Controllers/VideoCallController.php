<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\AgoraService;

class VideoCallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VideoCall::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'caller_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            // ...other fields...
        ]);
        $videoCall = VideoCall::create($data);
        return response()->json($videoCall, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VideoCall $videoCall)
    {
        return $videoCall;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VideoCall $videoCall)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VideoCall $videoCall)
    {
        $data = $request->validate([
            // ...fields...
        ]);
        $videoCall->update($data);
        return response()->json($videoCall);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VideoCall $videoCall)
    {
        $videoCall->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Generate Agora RTC token for a channel and user.
     */
    public function generateToken(Request $request, AgoraService $agoraService)
    {
        $request->validate([
            'channel_name' => 'required|string|max:255',
            'uid' => 'required|integer|min:1',
        ]);
        $token = $agoraService->generateRtcToken(
            $request->input('channel_name'),
            $request->input('uid'),
            30 * 60 // 30 minutes
        );
        return response()->json(['token' => $token]);
    }

    /**
     * Start a new video/voice call.
     */
    public function startCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'channel_name' => 'required|string|max:255',
            'type' => 'required|in:voice,video',
        ]);
        $call = VideoCall::create([
            'caller_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'channel_name' => $request->channel_name,
            'type' => $request->type,
            'status' => 'initiated',
            'created_at' => now(),
        ]);
        return response()->json($call, 201);
    }

    /**
     * End a call by call_id or channel_name.
     */
    public function endCall(Request $request)
    {
        $request->validate([
            'call_id' => 'nullable|integer|exists:video_calls,id',
            'channel_name' => 'nullable|string|max:255',
        ]);
        $call = null;
        if ($request->filled('call_id')) {
            $call = VideoCall::find($request->call_id);
        } elseif ($request->filled('channel_name')) {
            $call = VideoCall::where('channel_name', $request->channel_name)->latest()->first();
        }
        if (!$call) {
            return response()->json(['message' => 'Call not found'], 404);
        }
        $call->status = 'ended';
        $call->ended_at = Carbon::now();
        $call->save();
        return response()->json(['message' => 'Call ended', 'call' => $call]);
    }

    /**
     * Get recent call history for authenticated user.
     */
    public function getCallHistory(Request $request)
    {
        $userId = Auth::id();
        $calls = VideoCall::where('caller_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        return response()->json($calls);
    }
}
