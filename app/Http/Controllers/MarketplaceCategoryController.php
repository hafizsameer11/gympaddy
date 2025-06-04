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
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('MarketplaceCategory creation validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $category = MarketplaceCategory::create($validator->validated());
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
        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('MarketplaceCategory update validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation Failed',
                'errors' => collect($validator->errors())->map(function($messages, $field) {
                    return [
                        'field' => $field,
                        'reason' => $messages[0],
                        'suggestion' => 'Please provide a valid value'
                    ];
                })->values(),
            ], 422);
        }
        $marketplaceCategory->update($validator->validated());
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
