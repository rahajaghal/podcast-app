<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'channel_name'=>$this->name,
            'channel_image'=>asset($this->image),
            'description'=>$this->description,
            'channel_owner_id'=>$this->user_id,
            'channel_approve'=>$this->approved,
            'channel followers number'=>$this->users->count(),
        ];
    }
}
