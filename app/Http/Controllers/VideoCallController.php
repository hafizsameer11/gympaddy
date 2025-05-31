<?php

namespace App\Http\Controllers;

use App\Models\VideoCall;
use Illuminate\Http\Request;

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
}
