<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\Request;

class AdCampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdCampaign::all();
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
            // ...other fields...
        ]);
        $adCampaign = AdCampaign::create($data);
        return response()->json($adCampaign, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AdCampaign $adCampaign)
    {
        return $adCampaign;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdCampaign $adCampaign)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdCampaign $adCampaign)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            // ...other fields...
        ]);
        $adCampaign->update($data);
        return response()->json($adCampaign);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdCampaign $adCampaign)
    {
        $adCampaign->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
