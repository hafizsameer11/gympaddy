<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    private function formatAd(AdCampaign $ad): array
    {
        $adable = $ad->adable;
        $user   = $ad->user;
        $isPost = $ad->adable_type === \App\Models\Post::class;

        $impressions = $ad->insights()->sum('impressions') ?? 0;
        $clicks      = $ad->insights()->sum('clicks') ?? 0;

        // Resolve image: prefer non-empty media_url on campaign, then adable fields
        $image = !empty($ad->media_url) ? $ad->media_url : null;
        if (!$image && $adable) {
            if (!empty($adable->images)) {
                $raw = is_string($adable->images) ? json_decode($adable->images, true) : $adable->images;
                $image = is_array($raw) ? ($raw[0] ?? null) : null;
            }
            if (!$image) {
                $image = !empty($adable->media_url) ? $adable->media_url : null;
            }
            if (!$image) {
                $image = $adable->image ?? $adable->photo ?? null;
            }
        }

        // Price only makes sense for marketplace listings
        if ($isPost) {
            $price = '—';
        } else {
            $price = $adable && isset($adable->price)
                ? '₦' . number_format((float) $adable->price, 2)
                : '—';
        }

        $listingStatus = $adable ? ($adable->status ?? ($isPost ? 'published' : 'active')) : '—';

        return [
            'id'            => $ad->id,
            'image'         => $image,
            'name'          => $user->fullname ?? $ad->name ?? 'Unknown',
            'username'      => $user->username ?? null,
            'userImage'     => $user->profile_picture ?? null,
            'location'      => $ad->location ?? $user->location ?? 'Nigeria',
            'title'         => $ad->title ?? ($adable ? ($adable->title ?? $ad->name ?? '—') : ($ad->name ?? '—')),
            'price'         => $price,
            'description'   => $ad->content ?? ($adable ? ($adable->description ?? $adable->content ?? '') : ''),
            'category'      => $ad->type ?? ($adable ? ($adable->category ?? '—') : '—'),
            'type'          => $isPost ? 'post' : 'listing',
            'duration'      => $ad->duration ? "{$ad->duration} days" : '—',
            'date'          => $ad->created_at->format('d/m/y'),
            'dateCreated'   => $ad->created_at->format('d/m/y - h:i A'),
            'startDate'     => $ad->start_date?->format('d/m/y') ?? '—',
            'endDate'       => $ad->end_date?->format('d/m/y') ?? '—',
            'listingStatus' => $listingStatus,
            'adStatus'      => $ad->status ?? 'pending',
            'status'        => $ad->status ?? 'pending',
            'budget'        => (float) ($ad->budget ?? 0),
            'amountSpent'   => '₦' . number_format((float) ($ad->spent ?? $ad->budget ?? 0), 2),
            'boostDuration' => $ad->duration ? "{$ad->duration} days" : '—',
            'impressions'   => (int) $impressions,
            'clicks'        => (int) $clicks,
        ];
    }

    public function getAllAds(Request $request)
    {
        try {
            $query = AdCampaign::with(['user', 'adable', 'insights']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            $paginated = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $formatted = collect($paginated->items())->map(fn($ad) => $this->formatAd($ad));

            return response()->json([
                'success' => true,
                'data' => [
                    'ads' => $formatted,
                    'pagination' => [
                        'currentPage' => $paginated->currentPage(),
                        'totalPages'  => $paginated->lastPage(),
                        'totalItems'  => $paginated->total(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAdById($id)
    {
        try {
            $ad = AdCampaign::with(['user', 'adable', 'insights'])->find($id);
            if (!$ad) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ad not found']], 404);
            }
            return response()->json(['success' => true, 'data' => $this->formatAd($ad)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function createAd(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string',
                'description' => 'string',
                'type'        => 'required|string',
                'budget'      => 'required|numeric',
            ]);

            $ad = AdCampaign::create($validated);
            return response()->json(['success' => true, 'message' => 'Ad created successfully', 'data' => $ad]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateAd(Request $request, $id)
    {
        try {
            $ad = AdCampaign::find($id);
            if (!$ad) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ad not found']], 404);
            }
            $ad->update($request->all());
            return response()->json(['success' => true, 'message' => 'Ad updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function deleteAd($id)
    {
        try {
            $ad = AdCampaign::find($id);
            if (!$ad) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ad not found']], 404);
            }
            $ad->delete();
            return response()->json(['success' => true, 'message' => 'Ad deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function pauseAd($id)
    {
        try {
            $ad = AdCampaign::find($id);
            if (!$ad) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ad not found']], 404);
            }
            $ad->update(['status' => 'paused']);
            return response()->json(['success' => true, 'message' => 'Ad paused successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function resumeAd($id)
    {
        try {
            $ad = AdCampaign::find($id);
            if (!$ad) {
                return response()->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Ad not found']], 404);
            }
            $ad->update(['status' => 'active']);
            return response()->json(['success' => true, 'message' => 'Ad resumed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function getAdsStats()
    {
        try {
            $totalAds        = AdCampaign::count();
            $activeAds       = AdCampaign::where('status', 'active')->count();
            $pausedAds       = AdCampaign::where('status', 'paused')->count();
            $totalBudget     = AdCampaign::sum('budget');
            $totalImpressions = AdCampaign::join('ad_insights', 'ad_campaigns.id', '=', 'ad_insights.ad_campaign_id')
                ->sum('ad_insights.impressions') ?? 0;
            $totalClicks     = AdCampaign::join('ad_insights', 'ad_campaigns.id', '=', 'ad_insights.ad_campaign_id')
                ->sum('ad_insights.clicks') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalAds'         => $totalAds,
                    'activeAds'        => $activeAds,
                    'pausedAds'        => $pausedAds,
                    'totalBudget'      => (float) $totalBudget,
                    'totalImpressions' => (int) $totalImpressions,
                    'totalClicks'      => (int) $totalClicks,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
