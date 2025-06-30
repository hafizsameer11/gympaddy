<?php

namespace App\Http\Controllers;

use App\Helpers\RtcTokenBuilder;
use App\Models\VideoCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\AgoraService;
use Illuminate\Support\Facades\Log;

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

    public function generateLiveToken(Request $request, AgoraService $agoraService)
    {
        $request->validate([
            'channel_name' => 'required|string|max:255',
            'uid' => 'required|integer|min:1',
            'role' => 'required|in:host,audience', // 'host' = broadcaster, 'audience' = viewer
        ]);

        $token = $agoraService->generateRtcTokenWithRole(
            $request->input('channel_name'),
            $request->input('uid'),
            $request->input('role'),
            30 * 60 // 30 minutes
        );

        return response()->json(['token' => $token]);
    }
    public function getToken(Request $request)
    {
        try {
            $channel = $request->query('channel');
            $uid = intval($request->query('uid'));

            // ✅ Validate input
            if (!$channel || $uid <= 0) {
                return response()->json(['error' => 'Missing or invalid channel or uid'], 400);
            }

            // ✅ Load config safely
            $appId = '2fae578d9eef4fe19df335eb67227571';
            $appCertificate = '118e704beaea42e38b74b21a08bded63';

            if (!$appId || !$appCertificate) {
                return response()->json(['error' => 'Agora credentials are missing'], 500);
            }

            // ✅ Set token expiry (1 hour)
            $expireTimeInSeconds = 3600;
            $privilegeExpiredTs = time() + $expireTimeInSeconds;

            // ✅ Generate token
            $token = RtcTokenBuilder::buildTokenWithUid(
                $appId,
                $appCertificate,
                $channel,
                $uid,
                RtcTokenBuilder::RolePublisher, // Future: allow switching roles
                privilegeExpiredTs: $privilegeExpiredTs
            );
            Log::info('Agora token generated', [
                'channel' => $request->channel,
                'uid' => $request->uid,
                'token' => $token,
            ]);


            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            Log::error('Agora token generation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to generate token'], 500);
        }
    }
}
