<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'file_path',
        'file_name',
        'media_type',
        'mime_type',
        'file_size',
        'order',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    protected $appends = ['url'];
}
