<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreamGift extends Model
{
    use HasFactory;
    protected $fillable = [
        'live_stream_id',
        'sender_id',
        'gift_name',
        'gift_icon',
        'gift_value'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function live()
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }
}
