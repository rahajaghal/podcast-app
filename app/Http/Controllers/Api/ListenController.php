<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ListenResource;
use App\Http\Resources\PodcastResource;
use App\Models\Listen;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\FlareClient\Api;
use Symfony\Component\CssSelector\Parser\Handler\CommentHandler;

class ListenController extends Controller
{
    public function listenPodcast(Request $request)
    {
        $user = auth()->user();
        $podcast_id = $request->podcast_id;

        if ($user->podcasts()->where('podcast_id', $podcast_id)->exists()) {
            $user->podcasts()->detach($podcast_id);

            $userListen = Listen::create([
                'podcast_id' => $request->podcast_id,
                'user_id' => $user->id,
                'listening_duration' => $request->listening_duration,
            ]);
            $message = 'User Listen updated Successfully';
        } else {
            $userListen = Listen::create([
                'podcast_id' => $request->podcast_id,
                'user_id' => $user->id,
                'listening_duration' => $request->listening_duration,
            ]);

            $message = 'User Listen updated Successfully';
        }
        if($userListen){
            return ApiResponse::sendResponse('200',$message,new ListenResource($userListen));
        }
        return ApiResponse::sendResponse('200','user listens failed',[]);
    }
    public function userReview(ReviewRequest $request)
    {
        $user = auth()->user();
        $podcast_id = $request->podcast_id;
        $listen=DB::table('listens')->where('user_id',$user->id)
            ->where('podcast_id',$podcast_id)->limit(1);
        $listen->update([
           'rating'=>$request->rating,
           'comment'=>$request->comment,
            'updated_at'=>now(),
            'comment_time'=>now(),
        ]);
        return ApiResponse::sendResponse(200,'review successfully',[]);
    }
    public function podcastComments($podcast_id)
    {
       $podcastComments = Listen::where('podcast_id',$podcast_id)->whereNotNull('comment')->get();
       if ($podcastComments){
           return ApiResponse::sendResponse(200,'Podcast Comments Retrieved Successfully',
               CommentResource::collection($podcastComments));
       }
       return ApiResponse::sendResponse(200,'Podcast Comments Not Retrieved Successfully',[]);
    }
    public function top()
    {
        $topRanked=DB::table('listens')->select('podcast_id',
        DB::raw('AVG(rating) as average_rating'))
            ->groupBy('podcast_id')
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get();
        $podcastsIds=$topRanked->pluck('podcast_id');
        $topPodcasts=Podcast::whereIn('id',$podcastsIds)->get();
        if ($topPodcasts){
            return ApiResponse::sendResponse(200,'Top Podcast Retrieved Successfully',
                PodcastResource::collection($topPodcasts));
        }
        return ApiResponse::sendResponse(200,'Top Podcast Retrieved Successfully',[]);
    }
    public function mostListened()
    {
        $mostListened=DB::table('listens')->select('podcast_id',
            DB::raw('COUNT(user_id) as total_listens'))
            ->groupBy('podcast_id')
            ->orderByDesc('total_listens')
            ->limit(5)
            ->get();
        $podcastsIds=$mostListened->pluck('podcast_id');
        $podcasts=Podcast::whereIn('id',$podcastsIds)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,'Most  Podcast Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Top Podcast Retrieved Successfully',[]);
    }
}
