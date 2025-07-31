<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessService
{
    public function index()
    {
        return Business::all();
    }

    public function store($user, array $validated)
    {
        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending'; // Always force pending

        // Remove status if present in request (user cannot set)
        unset($validated['status']);

        // Handle photo upload if present
        if (isset($validated['photo']) && $validated['photo']) {
            $file = $validated['photo'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('business_photos', $filename, 'public');
            $validated['photo'] = $path;
        } else {
            unset($validated['photo']);
        }

        try {
            $business = Business::create($validated + ['status' => 'pending']);

            return response()->json([
                'status' => 'success',
                'code' => 201,
                'message' => 'Business registered successfully.',
                'data' => $business
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register business.',
                'code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Business $business)
    {
        return $business;
    }

    public function update(Business $business, array $validated)
    {
        // Only admin can update status (enforced in controller/middleware)
        try {
            $business->update($validated);

            return response()->json($business);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update business.',
                'code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function getBusinessStatus()
{
    $userId = Auth::id();
    $business = Business::where('user_id', $userId)->first();

    if ($business) {
        $isApproved = $business->status === 'approved';

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Business status retrieved successfully.',
            'data' => [
                'isApproved' => $isApproved,
                'status' => $business->status,
                'business' => $business
            ]
        ], 200);
    }

    // No business found – treat as not approved, but not an error
    return response()->json([
        'status' => 'success',
        'code' => 200,
        'message' => 'No business account found.',
        'data' => [
            'isApproved' => false,
            'status' => 'not_found',
            'business' => null
        ]
    ], 200);
}


    public function destroy(Business $business)
    {
        $business->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
