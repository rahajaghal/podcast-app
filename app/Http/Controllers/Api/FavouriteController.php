<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Models\Favourite;
use App\Models\Podcast;

class FavouriteController extends Controller
{
    public function toggleFavourite($podcast_id)
    {
        $user = auth()->user();
        if ($user->podcast()->where('podcast_id',$podcast_id)->exists()){
            $user->podcast()->detach($podcast_id);
            $message='Podcast Removed From Favourite Successfully';
        } else{
            $user->podcast()->attach($podcast_id);
            $message='Podcast Added To Favourite Successfully';
        }
        return ApiResponse::sendResponse(200,$message,[]);
    }

    public function favouritePodcasts()
    {
        $user = auth()->user();
        $favouritePodcasts=Favourite::where('user_id',$user->id)->pluck('podcast_id');
        $podcasts=Podcast::whereIn('id',$favouritePodcasts)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,"User Favourite Podcasts Retrieved Successfully",
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,"User Favourite Podcasts Not Retrieved Successfully",[]);
    }
    public function favouriteIds()
    {
        $user = auth()->user();
        $favouritePodcasts=Favourite::where('user_id',$user->id)->pluck('podcast_id');
        return $favouritePodcasts;
    }

}
