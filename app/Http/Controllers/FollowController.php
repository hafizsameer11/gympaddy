<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Follow::all();
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
            'follower_id' => 'required|integer',
            'followable_id' => 'required|integer',
            'followable_type' => 'required|string',
        ]);
        $follow = Follow::create($data);
        return response()->json($follow, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Follow $follow)
    {
        return $follow;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Follow $follow)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Follow $follow)
    {
        $data = $request->validate([
            // ...fields...
        ]);
        $follow->update($data);
        return response()->json($follow);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Follow $follow)
    {
        $follow->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
