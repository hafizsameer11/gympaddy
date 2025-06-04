<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Reel::all();
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
        $user = $request->user();
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'media_url' => 'required|string|max:2048',
            'thumbnail_url' => 'nullable|string|max:2048',
            'caption' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            Log::warning('Reel creation validation failed', ['errors' => $validator->errors()]);
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
        $data['user_id'] = $user->id;
        $reel = Reel::create($data);
        return response()->json($reel, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reel $reel)
    {
        return $reel;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reel $reel)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reel $reel)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'media_url' => 'sometimes|required|string|max:2048',
            'thumbnail_url' => 'nullable|string|max:2048',
            'caption' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            Log::warning('Reel update validation failed', ['errors' => $validator->errors()]);
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
        $reel->update($validator->validated());
        return response()->json($reel);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reel $reel)
    {
        $reel->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
