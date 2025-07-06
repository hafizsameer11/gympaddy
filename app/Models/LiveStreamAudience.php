<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreamAudience extends Model
{
    use HasFactory;
      protected $fillable = [
        'live_stream_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function live()
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }
}
