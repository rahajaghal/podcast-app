<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;
    protected $fillable=['title','podcast','podcast_duration','content_id','channel_id','size','approved','reported'];
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function usersDownload()
    {
        return $this->belongsToMany(User::class,'downloads','podcast_id','user_id');
    }
    public function user()
    {
        return $this->belongsToMany(User::class,'favourites','user_id','podcast_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class,'listens','podcast_id','user_id');
    }
    public function favourites()
    {
        return $this->hasMany(Favourite::class,'podcast_id');
    }
    public function ratings()
    {
        return $this->belongsToMany(User::class,'ratings','user_id','podcast_id');
    }
    public function rate()
    {
        return $this->hasMany(Rating::class,'podcast_id');
    }

}
