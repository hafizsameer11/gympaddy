<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Gift::all();
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
            'name' => 'required|string',
            'value' => 'required|numeric',
        ]);
        $gift = Gift::create($data);
        return response()->json($gift, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gift $gift)
    {
        return $gift;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gift $gift)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gift $gift)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'value' => 'sometimes|numeric',
        ]);
        $gift->update($data);
        return response()->json($gift);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gift $gift)
    {
        $gift->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
