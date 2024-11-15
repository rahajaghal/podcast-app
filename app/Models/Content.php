<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $fillable=['name'];
    public function podcasts()
    {
        return $this->hasMany(Podcast::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
