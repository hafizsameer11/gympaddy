<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function listings()
    {
        return $this->hasMany(MarketplaceListing::class, 'category_id');
    }
}
