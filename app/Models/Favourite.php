<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Favourite extends Pivot
{
    use HasFactory;
    protected $table='favourites';
    protected $fillable=['podcast_id','user_id'];
    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }
}
