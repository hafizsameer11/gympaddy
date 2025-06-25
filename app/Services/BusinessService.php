<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Database\QueryException;

class BusinessService
{
    public function index()
    {
        return Business::all();
    }

    public function store($user, array $validated)
    {
        $validated['user_id'] = $user->id;

        try {
            $business = Business::create($validated);

            return response()->json($business, 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                // Try to extract the duplicate field from the error message
                $field = 'unique field';
                if (preg_match('/for key \'([^\']+)\'/', $e->getMessage(), $matches)) {
                    $field = str_replace('businesses_', '', $matches[1]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => "The {$field} has already been taken.",
                    'code' => 409
                ], 409);
            }
            throw $e;
        }
    }

    public function show(Business $business)
    {
        return $business;
    }

    public function update(Business $business, array $validated)
    {
        try {
            $business->update($validated);

            return response()->json($business);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                $field = 'unique field';
                if (preg_match('/for key \'([^\']+)\'/', $e->getMessage(), $matches)) {
                    $field = str_replace('businesses_', '', $matches[1]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => "The {$field} has already been taken.",
                    'code' => 409
                ], 409);
            }
            throw $e;
        }
    }

    public function destroy(Business $business)
    {
        $business->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
