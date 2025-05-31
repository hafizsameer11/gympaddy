<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use Illuminate\Http\Request;

class MarketplaceListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MarketplaceListing::all();
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
        $listing = MarketplaceListing::create($data);
        return response()->json($listing, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketplaceListing $marketplaceListing)
    {
        return $marketplaceListing;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketplaceListing $marketplaceListing)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MarketplaceListing $marketplaceListing)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            // ...other fields...
        ]);
        $marketplaceListing->update($data);
        return response()->json($marketplaceListing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketplaceListing $marketplaceListing)
    {
        $marketplaceListing->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
