<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Listen extends Model
{
    use HasFactory;
    protected $table='listens';
    protected $fillable=['podcast_id','user_id','listening_duration','rating','comment','comment_time'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
