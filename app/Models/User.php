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
        'name',
        'email',
        'password',
        'status',
        'token',
        'is_admin',
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
    ];
    public function channel()
    {
        return $this->hasOne(Channel::class);
    }
    public function channels()
    {
        return $this->belongsToMany(Channel::class);
    }
    public function contents()
    {
        return $this->belongsToMany(Content::class);
    }
    public function download()
    {
        return $this->belongsToMany(Podcast::class,'downloads','user_id','podcast_id');
    }

    public function podcast()
    {
        return $this->belongsToMany(Podcast::class,'favourites','user_id','podcast_id');
    }
    public function podcasts()
    {
        return $this->belongsToMany(Podcast::class,'listens','user_id','podcast_id');
    }
    public function comments()
    {
        return $this->hasMany(Rating::class,'user_id');
    }
    public function ratings()
    {
        return $this->belongsToMany(Podcast::class,'ratings','user_id','podcast_id');
    }

}
