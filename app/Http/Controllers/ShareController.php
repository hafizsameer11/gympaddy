<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Share::all();
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
            'shareable_id' => 'required|integer',
            'shareable_type' => 'required|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $share = Share::create($data);
        return response()->json($share, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        return $share;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Share $share)
    {
        $data = $request->validate([
            // ...fields...
        ]);
        $share->update($data);
        return response()->json($share);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Share $share)
    {
        $share->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
