<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'budget' => 'required|numeric|min:0.01',
            'status' => 'nullable|string|in:pending,active,paused,completed,rejected',
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            Log::warning('AdCampaign creation validation failed', ['errors' => $validator->errors()]);
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
        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'budget' => 'sometimes|required|numeric|min:0.01',
            'status' => 'nullable|string|in:pending,active,paused,completed,rejected',
            // ...add other fields and constraints as needed...
        ]);
        if ($validator->fails()) {
            Log::warning('AdCampaign update validation failed', ['errors' => $validator->errors()]);
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
        $adCampaign->update($validator->validated());
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
