<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'phone',
        'age',
        'gender',
        'password',
        'role', // 'user' or 'admin'
        'profile_picture',
        'device_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function latestPost()
{
    return $this->hasOne(Post::class)->latestOfMany();
}

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function giftsSent()
    {
        return $this->hasMany(Gift::class, 'from_user_id');
    }

    public function giftsReceived()
    {
        return $this->hasMany(Gift::class, 'to_user_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the full URL for the profile picture
     */
    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : null;
    }

    protected $appends = ['profile_picture_url'];

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'related_user_id');
    }
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

public function latestImagePost()
{
    return $this->hasOne(Post::class)
        ->whereHas('media', function ($q) {
            $q->where('media_type', 'image');
        })
        ->latest(); // or orderByDesc('created_at')
}

    // ...add other relationships as needed...
}
