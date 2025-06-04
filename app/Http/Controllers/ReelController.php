<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use Illuminate\Http\Request;

class ReelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Reel::all();
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
        $user = $request->user();
        $data = $request->validate([
            'title' => 'required|string',
            'media_url' => 'required|string',
            'thumbnail_url' => 'nullable|string',
            'caption' => 'nullable|string',
        ]);
        $data['user_id'] = $user->id;
        $reel = Reel::create($data);
        return response()->json($reel, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reel $reel)
    {
        return $reel;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reel $reel)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reel $reel)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            // ...other fields...
        ]);
        $reel->update($data);
        return response()->json($reel);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reel $reel)
    {
        $reel->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
