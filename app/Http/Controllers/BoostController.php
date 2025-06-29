<?php

namespace App\Http\Controllers;

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

  public function boost(Post $post)
{
    $user = Auth::user();

    return $this->adCampaignService->boostFromPost($user, $post, []);
}
}
