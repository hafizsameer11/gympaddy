<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'media_url',
        'thumbnail_url',
        'caption',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function impressions()
    {
        return $this->morphMany(Impression::class, 'impressionable');
    }
}
