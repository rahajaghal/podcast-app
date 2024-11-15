<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user id'=>$this->user_id,
            'podcast_id'=>$this->podcast_id,
            'listening_duration'=>$this->listening_duration,
        ];
    }
}
