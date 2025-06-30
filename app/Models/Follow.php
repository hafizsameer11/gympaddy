<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followable_id',
        'followable_type',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }

    public function followable()
    {
        return $this->morphTo();
    }
}
