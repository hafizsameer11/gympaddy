<?php

namespace App\Http\Controllers;

use App\Models\StreamCall;
use App\Models\StreamToken;
use App\Services\PushNotificationService;
use App\Services\StreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamCallController extends Controller
{
    protected $stream;

    public function __construct(StreamService $stream)
    {
        $this->stream = $stream;
    }

    public function startCall(Request $request, PushNotificationService $pushService)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'call_type' => 'required|in:voice,video',
        ]);

        // Create call entry
        $callId = uniqid('call_');
        $caller = Auth::user();
        $call = StreamCall::create([
            'caller_id' => $caller->id,
            'receiver_id' => $request->receiver_id,
            'call_type' => $request->call_type,
            'status' => 'initiated',
            'callId' => $callId,
        ]);
        $token = $this->stream->createToken($caller->id);

        // Store/update token
        StreamToken::updateOrCreate(
            ['user_id' => $caller->id],
            ['token' => $token]
        );
        $caller = \App\Models\User::find($caller->id);
        $receiver = \App\Models\User::find($request->receiver_id);

        $title = "Incoming " . ucfirst($request->call_type) . " Call";
        $body = "{$caller->name} is calling you...";

        $pushService->sendToUserById(
            $receiver->id,
            $title,
            $body,
            data: [
                'call_id'    => $callId,
                'call_type'  => $request->call_type,
                'caller_id'  => $caller->id,
                'caller_name' => $caller->name,
                'type'       => 'incoming_call'
            ]
        );

        return response()->json([
            'call_id' => $callId,
            'token' => $token,
            'call_type' => $request->call_type,
            'receiver_id' => $request->receiver_id,
            'status' => 'pending'
        ]);
    }
    public function joinCall(Request $request)
    {
        $request->validate([

            'call_id' => 'required|string|exists:stream_calls,callId',
        ]);
        $user = Auth::user();
        $userId = $user->id;

        $call = StreamCall::where('callId', $request->call_id)->first();

        // Only receiver can join this call
        if ($call->receiver_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Generate Stream token for receiver
        // $client = new Client(env('STREAM_API_KEY'), env('STREAM_API_SECRET'));
        $token = $this->stream->createToken($userId);

        StreamToken::updateOrCreate(
            ['user_id' => $userId],
            ['token' => $token]
        );

        // Optionally update status to "accepted"
        $call->update(['status' => 'accepted']);

        return response()->json([
            'token' => $token,
            'call_id' => $call->callId,
            'caller_id' => $call->caller_id,
            'call_type' => $call->call_type,
            'status' => $call->status
        ]);
    }
}
