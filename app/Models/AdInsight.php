<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_campaign_id',
        'reach',
        'impressions',
        'clicks',
        // ...other fields...
    ];

    public function adCampaign()
    {
        return $this->belongsTo(AdCampaign::class);
    }
}
