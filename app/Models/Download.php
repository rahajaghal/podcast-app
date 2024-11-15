<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Download extends Pivot
{
    use HasFactory;
    protected $table='downloads';
    protected $fillable=['podcast_id','user_id'];
}
