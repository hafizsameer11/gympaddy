<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    use HasFactory;

    // Fields that can be mass-assigned
    protected $fillable = [
        'post_id',
        'file_path',
        'file_name',
        'media_type',
        'mime_type',
        'file_size',
        'order',
    ];

    // Add computed 'url' field to every response
    protected $appends = ['url'];

    // Define relationship to Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Accessor: returns full public URL for the media file
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
