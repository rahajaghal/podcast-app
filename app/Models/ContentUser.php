<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentUser extends Pivot
{
    use HasFactory;
    protected $fillable=['user_id','content_id'];
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
