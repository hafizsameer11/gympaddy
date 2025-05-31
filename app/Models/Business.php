<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status', // pending, approved, rejected
        // ...other fields...
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
