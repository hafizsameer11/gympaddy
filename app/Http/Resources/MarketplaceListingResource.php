<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'media_urls' => $this->media_urls,
            'price' => $this->price,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // ✅ only selected fields from related models
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'user' => [
                'name' => $this->user?->username,
                'profile_picture_url' => $this->user?->profile_picture_url,
            ],
        ];
    }
}

