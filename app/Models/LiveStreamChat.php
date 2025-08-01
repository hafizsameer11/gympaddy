<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreamChat extends Model
{
   use HasFactory;
   protected $fillable = [
      'user_id',
      'live_stream_id',
      'message',
      'created_at',
      'updated_at',
      'type',
         'reply_to_id',
   ];

   public function user()
   {
      return $this->belongsTo(User::class);
   }
   public function replyTo()
{
    return $this->belongsTo(LiveStreamChat::class, 'reply_to_id');
}
   public function live()
   {
      return $this->belongsTo(LiveStream::class, 'live_stream_id');
   }
}
