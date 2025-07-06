<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agora_channel',
        'title',
        'is_active',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
