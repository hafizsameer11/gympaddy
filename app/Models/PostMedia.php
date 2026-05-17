<?php

namespace App\Models;

use App\Services\PostMediaThumbnailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'file_path',
        'file_name',
        'thumbnail_path',
        'media_type',
        'mime_type',
        'file_size',
        'order',
    ];

    protected $appends = ['url', 'thumbnail', 'thumbnail_url'];

    protected $hidden = ['thumbnail_path'];

    protected static function booted(): void
    {
        static::deleting(function (PostMedia $media) {
            app(PostMediaThumbnailService::class)->deleteThumbnail($media->thumbnail_path);
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /** Full URL poster for video media; null for images or when not generated yet. */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->media_type !== 'video' || empty($this->thumbnail_path)) {
            return null;
        }

        return asset('storage/' . $this->thumbnail_path);
    }

    /** Alias for mobile clients that read `thumbnail`. */
    public function getThumbnailAttribute(): ?string
    {
        return $this->thumbnail_url;
    }
}
