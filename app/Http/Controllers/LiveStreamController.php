<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LiveStream::all();
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
            'title' => 'required|string',
            // ...other fields...
        ]);
        $liveStream = LiveStream::create($data);
        return response()->json($liveStream, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveStream $liveStream)
    {
        return $liveStream;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LiveStream $liveStream)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LiveStream $liveStream)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            // ...other fields...
        ]);
        $liveStream->update($data);
        return response()->json($liveStream);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveStream $liveStream)
    {
        $liveStream->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
