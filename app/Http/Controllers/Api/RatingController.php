<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ListenResource;
use App\Http\Resources\RatingResource;
use App\Models\Listen;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function userReview(ReviewRequest $request)
    {
        $user = auth()->user();
        $podcast_id = $request->podcast_id;

        if ($user->ratings()->where('podcast_id', $podcast_id)->exists()) {
            $user->ratings()->detach($podcast_id);

            $userRating = Rating::create([
                'podcast_id' => $request->podcast_id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment'=>$request->comment,
            ]);
            $message = 'User Rating updated Successfully';
        } else {
            $userRating = Rating::create([
                'podcast_id' => $request->podcast_id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment'=>$request->comment,
            ]);

            $message = 'User Rating updated Successfully';
        }
        if($userRating){
            return ApiResponse::sendResponse('200',$message,new RatingResource($userRating));
        }
        return ApiResponse::sendResponse('200','user listens failed',[]);
    }

    public function podcastComments($podcast_id)
    {
       $podcastComments = Rating::where('podcast_id',$podcast_id)->get();
       if ($podcastComments){
           return ApiResponse::sendResponse(200,'Podcast Comments Retrieved Successfully',
               CommentResource::collection($podcastComments));
       }
       return ApiResponse::sendResponse(200,'Podcast Comments Not Retrieved Successfully',[]);
    }
}
