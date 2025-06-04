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
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer|exists:marketplace_categories,id',
            'price' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,running,closed',
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            \Log::warning('MarketplaceListing creation validation failed', ['errors' => $validator->errors()]);
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
        $data = $validator->validated();
        $data['user_id'] = $request->user()->id;
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
        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            \Log::warning('MarketplaceListing update validation failed', ['errors' => $validator->errors()]);
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
        $marketplaceListing->update($validator->validated());
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
