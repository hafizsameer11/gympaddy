<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Http\Requests\StoreAdCampaignRequest;
use App\Http\Requests\UpdateAdCampaignRequest;
use App\Services\AdCampaignService;

class AdCampaignController extends Controller
{
    protected AdCampaignService $adCampaignService;

    public function __construct(AdCampaignService $adCampaignService)
    {
        $this->adCampaignService = $adCampaignService;
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return $this->adCampaignService->index();
    // }
    public function index()
    {
        return $this->adCampaignService->getBoostedCampaigns();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdCampaignRequest $request)
    {
        return $this->adCampaignService->store($request->user(), $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(AdCampaign $adCampaign)
    {
        // Eager load adable (Post or MarketplaceListing) and its media, plus user
        $adCampaign->load(['adable.media', 'user']);

        $adable = $adCampaign->adable;

        $response = [
            'id' => $adCampaign->id,
            'user_id' => $adCampaign->user_id,
            'name' => $adCampaign->name,
            'title' => $adCampaign->title,
            'content' => $adCampaign->content,
            'media_url' => $adCampaign->media_url,
            'budget' => $adCampaign->budget,
            'daily_budget' => $adCampaign->daily_budget,
            'duration' => $adCampaign->duration,
            'start_date' => $adCampaign->start_date,
            'end_date' => $adCampaign->end_date,
            'location' => $adCampaign->location,
            'age_min' => $adCampaign->age_min,
            'age_max' => $adCampaign->age_max,
            'gender' => $adCampaign->gender,
            'status' => $adCampaign->status,
            'type' => $adCampaign->type,
            'created_at' => $adCampaign->created_at,
            'updated_at' => $adCampaign->updated_at,
            'user' => [
                'id' => $adCampaign->user->id,
                'username' => $adCampaign->user->username,
                'fullname' => $adCampaign->user->fullname,
                'email' => $adCampaign->user->email,
                'phone' => $adCampaign->user->phone,
                'profile_picture_url' => $adCampaign->user->profile_picture_url,
            ],
        ];

        // Attach adable data (post or listing) with media
        if ($adable instanceof \App\Models\Post) {
            $response['post'] = [
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
        } elseif ($adable instanceof \App\Models\MarketplaceListing) {
            $response['listing'] = [
                'id' => $adable->id,
                'title' => $adable->title,
                'description' => $adable->description,
                'price' => $adable->price,
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

        return response()->json([
            'status' => 'success',
            'campaign' => $response
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdCampaign $adCampaign)
    {
        // Not used for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdCampaignRequest $request, AdCampaign $adCampaign)
    {
        return $this->adCampaignService->update($adCampaign, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdCampaign $adCampaign)
    {
        return $this->adCampaignService->destroy($adCampaign);
    }
}
