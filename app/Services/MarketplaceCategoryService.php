<?php

namespace App\Services;

use App\Models\MarketplaceCategory;
use Illuminate\Database\QueryException;

class MarketplaceCategoryService
{
    public function index()
    {
        return MarketplaceCategory::all();
    }

    public function store($validated)
    {
        try {
            $category = MarketplaceCategory::create($validated);
            return response()->json($category, 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate entry: this name already exists.',
                    'code' => 409
                ], 409);
            }
            throw $e;
        }
    }

    public function show(MarketplaceCategory $marketplaceCategory)
    {
        return $marketplaceCategory;
    }

    public function update(MarketplaceCategory $marketplaceCategory, $validated)
    {
        try {
            $marketplaceCategory->update($validated);
            return response()->json($marketplaceCategory);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate entry: this name already exists.',
                    'code' => 409
                ], 409);
            }
            throw $e;
        }
    }

    public function destroy(MarketplaceCategory $marketplaceCategory)
    {
        $marketplaceCategory->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
