<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;
    protected $fillable = [
        'caller_id',
        'receiver_id',
        'channel_name',
        'call_type',
        'status',
        'caller_uid', // Added for unique caller identification
        'receiver_uid', // Added for unique receiver identification
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getCallTypeAttribute($value)
    {
        return ucfirst($value);
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }
}
