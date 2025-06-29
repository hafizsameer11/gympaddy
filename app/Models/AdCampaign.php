<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'name',
        'title',
        'content',
        'media_url',
        'budget',
        'status',
        'type',
        // ...other fields...
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function insights()
    {
        return $this->hasMany(AdInsight::class);
    }

    public function isBoosted()
    {
        return $this->type === 'boosted';
    }
}
