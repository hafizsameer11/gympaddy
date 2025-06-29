<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'category',
        'address',
        'business_email',
        'business_phone',
        'photo',
        'description',
        'status'
        // 'status' is not fillable by user, only by admin
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
