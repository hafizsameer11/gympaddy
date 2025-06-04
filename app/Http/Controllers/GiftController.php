<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Gift::all();
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
            'to_user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0.01',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('Gift creation validation failed', ['errors' => $validator->errors()]);
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
        $data['from_user_id'] = $request->user()->id;
        $data['amount'] = $data['value'];
        $gift = Gift::create($data);
        return response()->json($gift, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gift $gift)
    {
        return $gift;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gift $gift)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gift $gift)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric|min:0.01',
            'message' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            Log::warning('Gift update validation failed', ['errors' => $validator->errors()]);
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
        $gift->update($validator->validated());
        return response()->json($gift);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gift $gift)
    {
        $gift->delete();
        return response()->json(['message' => 'Deleted']);
    }
     protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Always return JSON for API requests
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

}
