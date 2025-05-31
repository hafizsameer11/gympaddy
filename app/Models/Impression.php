<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impression extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'impressionable_id',
        'impressionable_type',
        'type', // e.g., 'view', 'click'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function impressionable()
    {
        return $this->morphTo();
    }
}
