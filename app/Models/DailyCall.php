<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyCall extends Model
{
    use HasFactory;
    protected $table = 'daily_calls';

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'channel_name',
        'room_url',
        'type',
        'status',
        'response'
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
