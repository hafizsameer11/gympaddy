<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LiveStream::all();
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
            'agora_channel' => 'required|string|max:255',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('LiveStream creation validation failed', ['errors' => $validator->errors()]);
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
        $liveStream = LiveStream::create($data);
        return response()->json($liveStream, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveStream $liveStream)
    {
        return $liveStream;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LiveStream $liveStream)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LiveStream $liveStream)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'agora_channel' => 'sometimes|required|string|max:255',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('LiveStream update validation failed', ['errors' => $validator->errors()]);
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
        $liveStream->update($validator->validated());
        return response()->json($liveStream);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveStream $liveStream)
    {
        $liveStream->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
