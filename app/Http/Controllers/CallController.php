<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function startCall(Request $request)
    {
         $data = $request->validate([
        'receiver_id' => 'required|integer',
        'channel_name' => 'required|string',
        'type' => 'required|string',
    ]);

    $callerUid = rand(100000, 999999);
    $receiverUid = rand(100000, 999999);

    $call = Call::create([
        'caller_id'    => auth()->id(),
        'receiver_id'  => $data['receiver_id'],
        'channel_name' => $data['channel_name'],
        'type'         => $data['type'],
        'status'       => 'Initiated', // default status
        'caller_uid'   => $callerUid,
        'receiver_uid' => $receiverUid,
    ]);

    return response()->json([
        'message' => 'Call started successfully',
        'call'    => $call,
    ]);
    }

    public function checkIncomingCall(Request $request)
    {
        $call = Call::where('receiver_id', auth()->id())
            ->where('status', 'initiated')
            ->latest()
            ->first();

        return response()->json(['call' => $call]);
    }

    public function endCall(Request $request)
    {
        $request->validate(['call_id' => 'required|integer']);
        $call = Call::find($request->call_id);
        if ($call) $call->update(['status' => 'ended']);
        return response()->json(['status' => 'ended']);
    }
}
