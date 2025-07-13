<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minute extends Model
{
    use HasFactory;
    protected $fillable=[
      'user_id',
      'live_stream_minute',
      'video_call_minute',
      'voice_call_minute'  
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
