<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;protected $fillable = [
    'user_id',
    'media_url',
    'media_type',
    'caption',
    'music_title',
    'music_url',
    'expires_at',
];

    /**
     * A story belongs to a user.
     */
     protected $appends = ['full_media_url'];

    /**
     * A story belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Accessor: full URL
    public function getFullMediaUrlAttribute()
    {
        return asset( $this->media_url);
    }
}
