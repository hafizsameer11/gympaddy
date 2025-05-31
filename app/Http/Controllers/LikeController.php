<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Like::all();
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
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $like = Like::create($data);
        return response()->json($like, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Like $like)
    {
        return $like;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Like $like)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Like $like)
    {
        $data = $request->validate([
            // ...fields...
        ]);
        $like->update($data);
        return response()->json($like);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Like $like)
    {
        $like->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
