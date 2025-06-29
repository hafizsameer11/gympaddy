<?php

namespace App\Services;

use App\Models\AdCampaign;
use App\Models\Post;

class AdCampaignService
{
    public function index()
    {
        return AdCampaign::all();
    }
   public function getBoostedCampaigns()
{
    return AdCampaign::with(['post.media', 'user']) // Eager load relations
        ->where('type', 'boosted')
        ->get()
        ->map(function ($campaign) {
            return [
                'id' => $campaign->id,
                'post_id' => $campaign->post_id,
                'user_id' => $campaign->user_id,
                'name' => $campaign->name,
                'title' => $campaign->title,
                'content' => $campaign->content,
                'media_url' => $campaign->media_url,
                'budget' => $campaign->budget,
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
                'post' => [
                    'id' => $campaign->post->id,
                    'title' => $campaign->post->title,
                    'content' => $campaign->post->content,
                    'media_type' => $campaign->post->media_type,
                    'is_boosted' => $campaign->post->is_boosted,
                    'created_at' => $campaign->post->created_at,
                    'media' => $campaign->post->media->map(function ($media) {
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
                ],
            ];
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
        // Only post owner can boost
        if ($post->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
                'code' => 403
            ], 403);
        }

        // Prevent duplicate boost
        if ($post->is_boosted || AdCampaign::where('post_id', $post->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This post is already boosted.',
                'code' => 409
            ], 409);
        }

        // Prepare campaign data
        $campaignData = [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'name' => $payload['name'] ?? 'Boosted: ' . ($post->title ?? 'Post'),
            'title' => $payload['title'] ?? $post->title ?? 'Untitled',
            'content' => $payload['content'] ?? $post->content ?? 'No content provided.',
            'media_url' => $payload['media_url'] ?? $post->media_url ?? '',
            'budget' => $payload['budget'] ?? 0,
            'status' => 'pending',
            'type' => 'boosted',
        ];

        $campaign = AdCampaign::create($campaignData);

        // Mark post as boosted
        $post->is_boosted = true;
        $post->save();

        return response()->json([
            'status' => 'success',
            'campaign' => $campaign
        ]);
    }
}
