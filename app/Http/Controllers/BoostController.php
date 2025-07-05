<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\MarketplaceListing;
use App\Models\Post;
use App\Services\AdCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoostController extends Controller
{
    protected AdCampaignService $adCampaignService;

    public function __construct(AdCampaignService $adCampaignService)
    {
        $this->middleware('auth:sanctum');
        $this->adCampaignService = $adCampaignService;
    }

    public function boostPost(Request $request,  $postId)
    {
        $user = Auth::user();
        $post = Post::findOrFail($postId);
        $payload = $request->validate([
            'name'          => 'nullable|string|max:255',
            'title'         => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'media_url'     => 'nullable|url',
            'budget'        => 'required|numeric|min:1',
            'daily_budget'  => 'nullable|numeric|min:1',
            'duration'      => 'nullable|integer|min:1',
            'location'      => 'nullable|string|max:255',
            'age_min'       => 'nullable|integer|min:13|max:100',
            'age_max'       => 'nullable|integer|min:13|max:100',
            'gender'        => 'nullable|in:all,male,female',
        ]);
        return $this->adCampaignService->boostFromPost($user, $post, $payload);
    }
    public function boostMarketplaceListing(Request $request, $listingId)
    {
        $user = Auth::user();
        $listing = MarketplaceListing::findOrFail($listingId);

        $payload = $request->validate([
            'name'          => 'nullable|string|max:255',
            'title'         => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'media_url'     => 'nullable|url',
            'budget'        => 'required|numeric|min:1',
            'daily_budget'  => 'nullable|numeric|min:1',
            'duration'      => 'nullable|integer|min:1',
            'location'      => 'nullable|string|max:255',
            'age_min'       => 'nullable|integer|min:13|max:100',
            'age_max'       => 'nullable|integer|min:13|max:100',
            'gender'        => 'nullable|in:all,male,female',
        ]);

        return $this->adCampaignService->boostFromMarketplaceListing($user, $listing, $payload);
    }
    public function updateBoostedPost(Request $request, $campaignId)
    {
        $user = Auth::user();
        $campaign = AdCampaign::where('user_id', $user->id)
            ->where('post_id', '!=', null)
            ->findOrFail($campaignId);

        $payload = $request->validate([
            'name'          => 'nullable|string|max:255',
            'title'         => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'media_url'     => 'nullable|url',
            'budget'        => 'nullable|numeric|min:1',
            'daily_budget'  => 'nullable|numeric|min:1',
            'duration'      => 'nullable|integer|min:1',
            'location'      => 'nullable|string|max:255',
            'age_min'       => 'nullable|integer|min:13|max:100',
            'age_max'       => 'nullable|integer|min:13|max:100',
            'gender'        => 'nullable|in:all,male,female',
        ]);

        return $this->adCampaignService->updatePostBoost($campaign, $payload);
    }
    public function updateBoostedMarketplace(Request $request, $campaignId)
    {
        $user = Auth::user();
        $campaign = AdCampaign::where('user_id', $user->id)
            ->where('marketplace_listing_id', '!=', null)
            ->findOrFail($campaignId);

        $payload = $request->validate([
            'name'          => 'nullable|string|max:255',
            'title'         => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'media_url'     => 'nullable|url',
            'budget'        => 'nullable|numeric|min:1',
            'daily_budget'  => 'nullable|numeric|min:1',
            'duration'      => 'nullable|integer|min:1',
            'location'      => 'nullable|string|max:255',
            'age_min'       => 'nullable|integer|min:13|max:100',
            'age_max'       => 'nullable|integer|min:13|max:100',
            'gender'        => 'nullable|in:all,male,female',
        ]);

        return $this->adCampaignService->updateMarketplaceBoost($campaign, $payload);
    }
    public function toggleCampaignStatus(Request $request, $campaignId)
    {
        $user = Auth::user();
        $request->validate([
            'action' => 'required|in:pause,resume',
        ]);

        $campaign = AdCampaign::where('user_id', $user->id)->findOrFail($campaignId);

        if ($request->action === 'pause') {
            $campaign->status = 'paused';
        } elseif ($request->action === 'resume') {
            $campaign->status = 'active';
        }

        $campaign->save();

        return response()->json([
            'message' => 'Campaign status updated successfully.',
            'status' => $campaign->status,
        ]);
    }
}
