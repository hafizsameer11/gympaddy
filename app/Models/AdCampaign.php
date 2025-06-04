<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'title',
        'content',
        'media_url',
        'budget',
        'status',
        // ...other fields...
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function insights()
    {
        return $this->hasMany(AdInsight::class);
    }
}
