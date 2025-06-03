<?php

namespace App\Http\Controllers;

use App\Models\AdInsight;
use Illuminate\Http\Request;

class AdInsightController extends Controller
{
    // List all ad insights, optionally filter by ad_campaign_id
    public function index(Request $request)
    {
        $query = AdInsight::query();

        if ($request->has('ad_campaign_id')) {
            $query->where('ad_campaign_id', $request->ad_campaign_id);
        }

        return response()->json($query->get());
    }

    // Show a single ad insight
    public function show($id)
    {
        $adInsight = AdInsight::findOrFail($id);
        return response()->json($adInsight);
    }
}
