<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'adable_id',
        'adable_type',
        'name',
        'title',
        'content',
        'media_url',
        'budget',
        'status',
        'type',

        // Targeting
        'location',
        'age_min',
        'age_max',
        'gender',

        // Budgeting & scheduling
        'daily_budget',
        'duration',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'budget' => 'float',
        'daily_budget' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'age_min' => 'integer',
        'age_max' => 'integer',
    ];

    /**
     * The user who created the campaign.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The thing being boosted (Post or MarketplaceListing).
     */
    public function adable()
    {
        return $this->morphTo();
    }

    /**
     * Related insights/metrics.
     */
    public function insights()
    {
        return $this->hasMany(AdInsight::class);
    }

    /**
     * Check if it's a Post boost.
     */
    public function isPostBoost(): bool
    {
        return $this->adable_type === \App\Models\Post::class;
    }

    /**
     * Check if it's a Listing boost.
     */
    public function isListingBoost(): bool
    {
        return $this->adable_type === \App\Models\MarketplaceListing::class;
    }

    /**
     * Check if it's currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Mark as completed if expired.
     */
    public function checkAndExpire()
    {
        if (now()->gt($this->end_date) && $this->status === 'active') {
            $this->update(['status' => 'completed']);
        }
    }
}
