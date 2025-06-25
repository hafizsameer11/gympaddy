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
    'description',
    'registration_number', // ← this was missing
    'status',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
