<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Business::all();
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
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            \Log::warning('Business creation validation failed', ['errors' => $validator->errors()]);
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
        $business = Business::create($validator->validated());
        return response()->json($business, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        return $business;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            \Log::warning('Business update validation failed', ['errors' => $validator->errors()]);
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
        $business->update($validator->validated());
        return response()->json($business);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        $business->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
