<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;

class MarketplaceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MarketplaceCategory::all();
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
        $category = MarketplaceCategory::create($data);
        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketplaceCategory $marketplaceCategory)
    {
        return $marketplaceCategory;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketplaceCategory $marketplaceCategory)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MarketplaceCategory $marketplaceCategory)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            // ...other fields...
        ]);
        $marketplaceCategory->update($data);
        return response()->json($marketplaceCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketplaceCategory $marketplaceCategory)
    {
        $marketplaceCategory->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
