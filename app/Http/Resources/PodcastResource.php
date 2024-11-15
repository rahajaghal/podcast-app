<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'podcast id'=>$this->id,
            'podcast title'=>$this->title,
            'podcast path'=>asset($this->podcast),
            'podcast duration'=>$this->podcast_duration,
            'podcast size'=>$this->size,
            'channel id'=>$this->channel->id,
            'channel name'=>$this->channel->name,
            'channel image'=>asset($this->channel->image),
            'channel owner name'=>$this->channel->user->name,
            'podcast favourites'=>$this->favourites->count(),
            'podcast listeners number'=>$this->users->count(),
            'podcast rate'=>$this->rate->avg('rating'),
        ];
    }
}
