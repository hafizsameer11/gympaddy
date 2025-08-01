<?php

namespace App\Services;

use App\Models\AdCampaign;
use App\Models\MarketplaceListing;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class AdCampaignService
{
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
        $adCampaign->delete();
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

        // Prevent duplicate boost
        if ($post->is_boosted || $post->adCampaigns()->where('status', '!=', 'completed')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post is already boosted.',
                'code' => 409
            ], 409);
        }

        // Create campaign via morphMany relation
        $campaign = $post->adCampaigns()->create([
            'user_id'     => $user->id,
            'name'        => $payload['name'] ?? 'Boosted: ' . ($post->title ?? 'Post'),
            'title'       => $payload['title'] ?? $post->title ?? 'Untitled',
            'content'     => $payload['content'] ?? $post->content ?? 'No content provided.',
            'media_url'   => $payload['media_url'] ?? $post->media_url ?? '',
            'budget'      => $payload['budget'] ?? 0,
            'daily_budget' => $payload['daily_budget'] ?? 0,
            'duration'    => $payload['duration'] ?? 1,
            'start_date'  => now(),
            'end_date'    => now()->addDays($payload['duration'] ?? 1),
            'location'    => $payload['location'] ?? null,
            'age_min'     => $payload['age_min'] ?? 18,
            'age_max'     => $payload['age_max'] ?? 65,
            'gender'      => $payload['gender'] ?? 'all',
            'status'      => 'pending',
            'type'        => 'boost_post',
        ]);

        // Mark post as boosted
        $post->is_boosted = true;
        $post->save();

        return response()->json([
            'status' => 'success',
            'campaign' => $campaign
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

        // Prevent duplicate boost
        if ($listing->adCampaigns()->where('status', '!=', 'completed')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This listing is already boosted.',
                'code' => 409
            ], 409);
        }

        // Create campaign
        $campaign = $listing->adCampaigns()->create([
            'user_id'     => $user->id,
            'name'        => $payload['name'] ?? 'Boosted: ' . ($listing->title ?? 'Listing'),
            'title'       => $payload['title'] ?? $listing->title ?? 'Untitled',
            'content'     => $payload['content'] ?? $listing->description ?? 'No content provided.',
            'media_url'   => $payload['media_url'] ?? null,
            'budget'      => $payload['budget'] ?? 0,
            'daily_budget' => $payload['daily_budget'] ?? 0,
            'duration'    => $payload['duration'] ?? 1,
            'start_date'  => now(),
            'end_date'    => now()->addDays($payload['duration'] ?? 1),
            'location'    => $payload['location'] ?? null,
            'age_min'     => $payload['age_min'] ?? 18,
            'age_max'     => $payload['age_max'] ?? 65,
            'gender'      => $payload['gender'] ?? 'all',
            'status'      => 'pending',
            'type'        => 'boost_listing',
        ]);

        return response()->json([
            'status' => 'success',
            'campaign' => $campaign
        ]);
    }
    public function updatePostBoost(AdCampaign $campaign, array $payload)
{
    // if ($campaign->type !== 'boost_post') {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Invalid campaign type.',
    //     ], 422);
    // }

    $campaign->update([
        'name'         => $payload['name'] ?? $campaign->name,
        'title'        => $payload['title'] ?? $campaign->title,
        'content'      => $payload['content'] ?? $campaign->content,
        'media_url'    => $payload['media_url'] ?? $campaign->media_url,
        'budget'       => $payload['budget'] ?? $campaign->budget,
        'daily_budget' => $payload['daily_budget'] ?? $campaign->daily_budget,
        'duration'     => $payload['duration'] ?? $campaign->duration,
        'end_date'     => now()->addDays($payload['duration'] ?? $campaign->duration),
        'location'     => $payload['location'] ?? $campaign->location,
        'age_min'      => $payload['age_min'] ?? $campaign->age_min,
        'age_max'      => $payload['age_max'] ?? $campaign->age_max,
        'gender'       => $payload['gender'] ?? $campaign->gender,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Post campaign updated successfully.',
        'campaign' => $campaign
    ]);
}
public function updateMarketplaceBoost(AdCampaign $campaign, array $payload)
{
    // if ($campaign->type !== 'boost_listing') {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Invalid campaign type.',
    //     ], 422);
    // }

    $campaign->update([
        'name'         => $payload['name'] ?? $campaign->name,
        'title'        => $payload['title'] ?? $campaign->title,
        'content'      => $payload['content'] ?? $campaign->content,
        'media_url'    => $payload['media_url'] ?? $campaign->media_url,
        'budget'       => $payload['budget'] ?? $campaign->budget,
        'daily_budget' => $payload['daily_budget'] ?? $campaign->daily_budget,
        'duration'     => $payload['duration'] ?? $campaign->duration,
        'end_date'     => now()->addDays($payload['duration'] ?? $campaign->duration),
        'location'     => $payload['location'] ?? $campaign->location,
        'age_min'      => $payload['age_min'] ?? $campaign->age_min,
        'age_max'      => $payload['age_max'] ?? $campaign->age_max,
        'gender'       => $payload['gender'] ?? $campaign->gender,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Marketplace campaign updated successfully.',
        'campaign' => $campaign
    ]);
}


}
