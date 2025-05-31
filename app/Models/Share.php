<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shareable_id',
        'shareable_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shareable()
    {
        return $this->morphTo();
    }
}
