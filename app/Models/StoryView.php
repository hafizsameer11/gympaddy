<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryView extends Model
{
    use HasFactory;
    protected $fillable = [
        'story_id',
        'user_id',
        'viewed_at',
    ];

    /**
     * A story view belongs to a story.
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * A story view belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
