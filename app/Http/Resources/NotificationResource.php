<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "podcast_id"=>$this->data['podcast_id'],
            "podcast_title"=>$this->data['podcast_title'],
            "channel_name"=>$this->data['channel_name'],
            "channel_image"=>asset($this->data['channel_image']),
            "created_at"=>$this->created_at,
        ];
    }
}
