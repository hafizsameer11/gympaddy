<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Notification::all();
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
            'message' => 'required|string|max:1000',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('Notification creation validation failed', ['errors' => $validator->errors()]);
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
        $notification = Notification::create($data);
        return response()->json($notification, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        return $notification;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        $validator = \Validator::make($request->all(), [
            'message' => 'sometimes|required|string|max:1000',
            // ...other fields...
        ]);
        if ($validator->fails()) {
            \Log::warning('Notification update validation failed', ['errors' => $validator->errors()]);
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
        $notification->update($validator->validated());
        return response()->json($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
