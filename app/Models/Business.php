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
        'status',
        'notes',
        'rejected_reason',
        'approved_by',
        'rejected_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Admin who approved this business verification
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Admin who rejected this business verification
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
