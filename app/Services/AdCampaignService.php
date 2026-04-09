<?php

namespace App\Services;

use App\Models\AdCampaign;
use App\Models\MarketplaceListing;
use App\Models\Post;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdCampaignService
{
    private const ACTIVE_CAMPAIGN_STATUSES = ['pending', 'active', 'paused'];

    /**
     * Total GP charged upfront = daily budget (slider) × duration (days).
     */
    private function computeBoostTotalGp(float $dailyGp, int $durationDays): float
    {
        $durationDays = max(1, $durationDays);

        return round($dailyGp * $durationDays, 2);
    }

    /**
     * Total GP that was allocated for this campaign (for wallet delta on edit).
     * New rows: daily_budget × duration (matches budget field).
     * Legacy: daily_budget empty and budget stored the daily slider value.
     */
    private function campaignTotalGpAllocated(AdCampaign $campaign): float
    {
        $duration = max(1, (int) $campaign->duration);
        $daily = (float) $campaign->daily_budget;
        if ($daily > 0) {
            return round($daily * $duration, 2);
        }

        return round((float) $campaign->budget * $duration, 2);
    }

    private function campaignDailyGp(AdCampaign $campaign): float
    {
        $daily = (float) $campaign->daily_budget;
        if ($daily > 0) {
            return $daily;
        }

        return (float) $campaign->budget;
    }

    private function insufficientBalanceResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Insufficient GP balance to run this boost. Please top up your wallet.',
            'code' => 422,
        ], 422);
    }

    /**
     * Debit user wallet (must run inside an outer DB transaction).
     */
    private function debitWalletForBoost($user, float $gpAmount, array $meta): void
    {
        if ($gpAmount <= 0) {
            return;
        }

        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if ((float) $wallet->balance < $gpAmount) {
            throw new HttpResponseException($this->insufficientBalanceResponse());
        }

        $wallet->balance = (float) $wallet->balance - $gpAmount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'purchase',
            'amount' => $gpAmount,
            'reference' => null,
            'related_user_id' => null,
            'meta' => json_encode($meta),
            'status' => 'completed',
        ]);
    }

    private function creditWalletForBoost($user, float $gpAmount, array $meta): void
    {
        if ($gpAmount <= 0) {
            return;
        }

        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
        $wallet->balance = (float) $wallet->balance + $gpAmount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'other',
            'amount' => $gpAmount,
            'reference' => null,
            'related_user_id' => null,
            'meta' => json_encode(array_merge($meta, ['adjustment' => 'boost_budget_change'])),
            'status' => 'completed',
        ]);
    }

    private function hasBlockingCampaign($adable, ?int $excludeCampaignId = null): bool
    {
        if (!$adable || !method_exists($adable, 'adCampaigns')) {
            return false;
        }

        $query = $adable->adCampaigns()->whereIn('status', self::ACTIVE_CAMPAIGN_STATUSES);
        if ($excludeCampaignId !== null) {
            $query->where('id', '!=', $excludeCampaignId);
        }

        return $query->exists();
    }

    private function syncPostBoostFlag($adable, ?int $excludeCampaignId = null): void
    {
        if (!($adable instanceof Post)) {
            return;
        }

        $shouldBeBoosted = $this->hasBlockingCampaign($adable, $excludeCampaignId);
        if ((bool) $adable->is_boosted !== $shouldBeBoosted) {
            $adable->is_boosted = $shouldBeBoosted;
            $adable->save();
        }
    }

    public function index()
    {
        return AdCampaign::all();
    }
 public function getBoostedCampaigns()
{
    $user=Auth::user();
        return AdCampaign::with(['adable', 'user'])
        ->orderBy('created_at', 'desc')
        ->where('user_id',$user->id)
        ->get()
        ->map(function ($campaign) {
            $adable = $campaign->adable;

            $baseData = [
                'id' => $campaign->id,
                'user_id' => $campaign->user_id,
                'name' => $campaign->name,
                'title' => $campaign->title,
                'content' => $campaign->content,
                'media_url' => $campaign->media,
                'budget' => $campaign->budget,
                'gender'=>$campaign->gender,
        'daily_budget' => $campaign->daily_budget,
        'duration' => $campaign->duration,
        'start_date' => $campaign->start_date,
        'end_date' => $campaign->end_date,
        'location' => $campaign->location,
        'age_min' => $campaign->age_min,
        'age_max' => $campaign->age_max,
                'status' => $campaign->status,
                'type' => $campaign->type,
                'created_at' => $campaign->created_at,
                'updated_at' => $campaign->updated_at,
                'user' => [
                    'id' => $campaign->user->id,
                    'username' => $campaign->user->username,
                    'fullname' => $campaign->user->fullname,
                    'email' => $campaign->user->email,
                    'phone' => $campaign->user->phone,
                    'profile_picture_url' => $campaign->user->profile_picture_url,
                ],
            ];

            // Handle Post (has a media relation)
            if ($adable instanceof \App\Models\Post) {
                $baseData['post'] = [
                    'id' => $adable->id,
                    'title' => $adable->title,
                    'content' => $adable->content,
                    'media_type' => $adable->media_type,
                    'is_boosted' => $adable->is_boosted,
                    'created_at' => $adable->created_at,
                    'media' => $adable->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'file_name' => $media->file_name,
                            'media_type' => $media->media_type,
                            'mime_type' => $media->mime_type,
                            'file_size' => $media->file_size,
                            'order' => $media->order,
                            'url' => $media->url,
                        ];
                    }),
                ];
            }

            // Handle Marketplace Listing (media stored as array)
            elseif ($adable instanceof \App\Models\MarketplaceListing) {
                $baseData['listing'] = [
                    'id' => $adable->id,
                    'title' => $adable->title,
                    'description' => $adable->description,
                    'price' => $adable->price,
                    'is_boosted' => $adable->is_boosted,
                    'created_at' => $adable->created_at,
                    'media' => collect($adable->media_urls)->map(function ($url, $index) {
                        return [
                            'id' => null,
                            'file_name' => basename($url),
                            'media_type' => 'image', // You can update logic to detect video etc.
                            'mime_type' => null,
                            'file_size' => null,
                            'order' => $index,
                            'url' => $url,
                        ];
                    }),
                ];
            }

            return $baseData;
        });
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
        //return ad compaign with dproper data of media

        return $adCampaign;
    }

    public function update(AdCampaign $adCampaign, $validated)
    {
        $adCampaign->update($validated);
        return response()->json($adCampaign);
    }

    public function destroy(AdCampaign $adCampaign)
    {
        $adable = $adCampaign->adable;
        $adCampaign->delete();
        // Force fresh DB check so post/listing is no longer considered boosted
        if (method_exists($adable, 'unsetRelation')) {
            $adable->unsetRelation('adCampaigns');
        }
        $this->syncPostBoostFlag($adable, null);
        return response()->json(['message' => 'Deleted']);
    }

    public function boostFromPost($user, Post $post, array $payload)
    {
        // Ensure the post belongs to the user
        if ($post->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
                'code' => 403
            ], 403);
        }

        // Use fresh campaign data from DB so re-boost works after a previous campaign was deleted
        $post->unsetRelation('adCampaigns');
        $this->syncPostBoostFlag($post);
        if ($this->hasBlockingCampaign($post)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post is already boosted.',
                'code' => 409
            ], 409);
        }

        $dailyGp = (float) ($payload['budget'] ?? 0);
        $duration = max(1, (int) ($payload['duration'] ?? 1));
        $totalGp = $this->computeBoostTotalGp($dailyGp, $duration);

        $campaign = null;

        DB::transaction(function () use ($user, $post, $payload, $dailyGp, $duration, $totalGp, &$campaign) {
            $this->debitWalletForBoost($user, $totalGp, [
                'context' => 'boost_post',
                'post_id' => $post->id,
                'daily_budget_gp' => $dailyGp,
                'duration_days' => $duration,
                'total_gp' => $totalGp,
            ]);

            $campaign = $post->adCampaigns()->create([
                'user_id'     => $user->id,
                'name'        => $payload['name'] ?? 'Boosted: ' . ($post->title ?? 'Post'),
                'title'       => $payload['title'] ?? $post->title ?? 'Untitled',
                'content'     => $payload['content'] ?? $post->content ?? 'No content provided.',
                'media_url'   => $payload['media_url'] ?? $post->media_url ?? '',
                'budget'      => $totalGp,
                'daily_budget' => $dailyGp,
                'duration'    => $duration,
                'start_date'  => now(),
                'end_date'    => now()->addDays($duration),
                'location'    => $payload['location'] ?? null,
                'age_min'     => $payload['age_min'] ?? 18,
                'age_max'     => $payload['age_max'] ?? 65,
                'gender'      => $payload['gender'] ?? 'all',
                'status'      => 'pending',
                'type'        => 'boost_post',
            ]);

            $post->is_boosted = true;
            $post->save();
        });

        return response()->json([
            'status' => 'success',
            'campaign' => $campaign,
            'charged_gp' => $totalGp,
        ]);
    }
    public function boostFromMarketplaceListing($user, MarketplaceListing $listing, array $payload)
    {
        // Ensure the listing belongs to the user
        if ($listing->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
                'code' => 403
            ], 403);
        }

        // Use fresh campaign data from DB so re-boost works after a previous campaign was deleted
        $listing->unsetRelation('adCampaigns');
        if ($this->hasBlockingCampaign($listing)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This listing is already boosted.',
                'code' => 409
            ], 409);
        }

        $dailyGp = (float) ($payload['budget'] ?? 0);
        $duration = max(1, (int) ($payload['duration'] ?? 1));
        $totalGp = $this->computeBoostTotalGp($dailyGp, $duration);

        $campaign = null;

        DB::transaction(function () use ($user, $listing, $payload, $dailyGp, $duration, $totalGp, &$campaign) {
            $this->debitWalletForBoost($user, $totalGp, [
                'context' => 'boost_listing',
                'listing_id' => $listing->id,
                'daily_budget_gp' => $dailyGp,
                'duration_days' => $duration,
                'total_gp' => $totalGp,
            ]);

            $campaign = $listing->adCampaigns()->create([
                'user_id'     => $user->id,
                'name'        => $payload['name'] ?? 'Boosted: ' . ($listing->title ?? 'Listing'),
                'title'       => $payload['title'] ?? $listing->title ?? 'Untitled',
                'content'     => $payload['content'] ?? $listing->description ?? 'No content provided.',
                'media_url'   => $payload['media_url'] ?? null,
                'budget'      => $totalGp,
                'daily_budget' => $dailyGp,
                'duration'    => $duration,
                'start_date'  => now(),
                'end_date'    => now()->addDays($duration),
                'location'    => $payload['location'] ?? null,
                'age_min'     => $payload['age_min'] ?? 18,
                'age_max'     => $payload['age_max'] ?? 65,
                'gender'      => $payload['gender'] ?? 'all',
                'status'      => 'pending',
                'type'        => 'boost_listing',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'campaign' => $campaign,
            'charged_gp' => $totalGp,
        ]);
    }
    public function updatePostBoost(AdCampaign $campaign, array $payload)
{
    $user = Auth::user();

    $newDaily = isset($payload['budget']) ? (float) $payload['budget'] : $this->campaignDailyGp($campaign);
    $newDuration = isset($payload['duration']) ? max(1, (int) $payload['duration']) : max(1, (int) $campaign->duration);
    $newTotal = $this->computeBoostTotalGp($newDaily, $newDuration);
    $oldTotal = $this->campaignTotalGpAllocated($campaign);
    $delta = round($newTotal - $oldTotal, 2);

    DB::transaction(function () use ($user, $campaign, $payload, $newDaily, $newDuration, $newTotal, $delta) {
        if ($delta > 0.0001) {
            $this->debitWalletForBoost($user, $delta, [
                'context' => 'boost_post_update',
                'campaign_id' => $campaign->id,
                'additional_gp' => $delta,
            ]);
        } elseif ($delta < -0.0001) {
            $this->creditWalletForBoost($user, abs($delta), [
                'context' => 'boost_post_update',
                'campaign_id' => $campaign->id,
                'refunded_gp' => abs($delta),
            ]);
        }

        $campaign->update([
            'name'         => $payload['name'] ?? $campaign->name,
            'title'        => $payload['title'] ?? $campaign->title,
            'content'      => $payload['content'] ?? $campaign->content,
            'media_url'    => $payload['media_url'] ?? $campaign->media_url,
            'budget'       => $newTotal,
            'daily_budget' => $newDaily,
            'duration'     => $newDuration,
            'end_date'     => now()->addDays($newDuration),
            'location'     => $payload['location'] ?? $campaign->location,
            'age_min'      => $payload['age_min'] ?? $campaign->age_min,
            'age_max'      => $payload['age_max'] ?? $campaign->age_max,
            'gender'       => $payload['gender'] ?? $campaign->gender,
        ]);
    });

    $campaign->refresh();

    return response()->json([
        'status' => 'success',
        'message' => 'Post campaign updated successfully.',
        'campaign' => $campaign,
        'wallet_delta_gp' => $delta,
    ]);
}
public function updateMarketplaceBoost(AdCampaign $campaign, array $payload)
{
    $user = Auth::user();

    $newDaily = isset($payload['budget']) ? (float) $payload['budget'] : $this->campaignDailyGp($campaign);
    $newDuration = isset($payload['duration']) ? max(1, (int) $payload['duration']) : max(1, (int) $campaign->duration);
    $newTotal = $this->computeBoostTotalGp($newDaily, $newDuration);
    $oldTotal = $this->campaignTotalGpAllocated($campaign);
    $delta = round($newTotal - $oldTotal, 2);

    DB::transaction(function () use ($user, $campaign, $payload, $newDaily, $newDuration, $newTotal, $delta) {
        if ($delta > 0.0001) {
            $this->debitWalletForBoost($user, $delta, [
                'context' => 'boost_listing_update',
                'campaign_id' => $campaign->id,
                'additional_gp' => $delta,
            ]);
        } elseif ($delta < -0.0001) {
            $this->creditWalletForBoost($user, abs($delta), [
                'context' => 'boost_listing_update',
                'campaign_id' => $campaign->id,
                'refunded_gp' => abs($delta),
            ]);
        }

        $campaign->update([
            'name'         => $payload['name'] ?? $campaign->name,
            'title'        => $payload['title'] ?? $campaign->title,
            'content'      => $payload['content'] ?? $campaign->content,
            'media_url'    => $payload['media_url'] ?? $campaign->media_url,
            'budget'       => $newTotal,
            'daily_budget' => $newDaily,
            'duration'     => $newDuration,
            'end_date'     => now()->addDays($newDuration),
            'location'     => $payload['location'] ?? $campaign->location,
            'age_min'      => $payload['age_min'] ?? $campaign->age_min,
            'age_max'      => $payload['age_max'] ?? $campaign->age_max,
            'gender'       => $payload['gender'] ?? $campaign->gender,
        ]);
    });

    $campaign->refresh();

    return response()->json([
        'status' => 'success',
        'message' => 'Marketplace campaign updated successfully.',
        'campaign' => $campaign,
        'wallet_delta_gp' => $delta,
    ]);
}


}
