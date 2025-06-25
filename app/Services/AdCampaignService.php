<?php

namespace App\Services;

use App\Models\AdCampaign;

class AdCampaignService
{
    public function index()
    {
        return AdCampaign::all();
    }

    public function store($user, $validated)
    {
        $data = $validated;
        $data['user_id'] = $user->id;
        $adCampaign = AdCampaign::create($data);
        return response()->json($adCampaign, 201);
    }

    public function show(AdCampaign $adCampaign)
    {
        return $adCampaign;
    }

    public function update(AdCampaign $adCampaign, $validated)
    {
        $adCampaign->update($validated);
        return response()->json($adCampaign);
    }

    public function destroy(AdCampaign $adCampaign)
    {
        $adCampaign->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
