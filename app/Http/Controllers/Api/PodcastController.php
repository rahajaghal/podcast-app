<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PodcastRequest;
use App\Http\Requests\TokenRequest;
use App\Http\Resources\ContentUserResource;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\TagResource;
use App\Models\Channel;
use App\Models\ContentUser;
use App\Models\Download;
use App\Models\Listen;
use App\Models\Podcast;
use App\Models\PodcastTag;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\CreatePodcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\FlareClient\Api;
use Illuminate\Support\Facades\Notification;
use function Laravel\Prompts\table;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class PodcastController extends Controller
{
    public function showHomeUser()
    {
        $user = auth()->user();
        $userContents=ContentUser::where('user_id',$user->id)->get();
        if (count($userContents)>0){
            return ApiResponse::sendResponse(200,'user contents Retrieved Successfully',ContentUserResource::collection($userContents));
        }
        return ApiResponse::sendResponse(200,'user contents Not Retrieved Successfully',[]);
    }
    public function latestPodcast()
    {
        $user=auth()->user();
        $latestpodcast=Listen::where('user_id',$user->id)->latest()->limit(1)->pluck('podcast_id');
        $latestpodcast=$latestpodcast[0];

        $podcast=Podcast::where('id',$latestpodcast)->get()->first();
        if ($podcast){
            return ApiResponse::sendResponse(200,'User Latest Podcast Retrieved Successfully',new PodcastResource($podcast));
        }
        return ApiResponse::sendResponse(200,'User Latest Podcast Not Retrieved Successfully',[]);
    }
    public function showBasedContent(Request $request)
    {
        if (count($request->contentId)==0){
            $podcasts=Podcast::all();
        }else{
            $podcasts=Podcast::where('content_id',$request->contentId)->where('approved',1)->get();
        }
        if($podcasts){
            return ApiResponse::sendResponse(200,'Podcasts Retrieved Successfully',PodcastResource::
            collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Podcasts Not Found',[]);
    }
    public function showBasedContentWithUser(Request $request){
        if (count($request->contentId)==1){
            $podcasts=Podcast::where('content_id',$request->contentId)->where('approved',1)->get();
        }else{
            $podcasts=Podcast::whereIn('content_id',$request->contentId)->where('approved',1)->get();
        }
        if(count($podcasts)>0){
            return ApiResponse::sendResponse(200,'Podcasts Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Podcasts Not Found',[]);
    }

    public function searchBasedTitle(Request $request)
    {
        $word=$request->has('search') ? $request->input('search') : null;
        $podcasts=Podcast::when($word!=null,function ($q) use ($word){
            $q->where('title','like','%'.$word.'%')->where('approved',1);
        })->latest()->get();
        if (count($podcasts)>0){
            return ApiResponse::sendResponse(200,'Search Completed',PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'No Matching',null);
    }
    #-------try this function
    public function upload(PodcastRequest $request)
    {
        $podcast =$request->file('podcast')->getClientOriginalName();
        $path=$request->file('podcast')->storeAs('podcasts',$podcast,'imgs');
        $data=$request->validated();
        $data['podcast']=$path;
        ###-------get channel id
        $channelId= Channel::where('user_id',auth()->user()->id)->pluck('id');
        $data['channel_id']=$channelId[0];
        $data['size']=$request->file('podcast')->getSize()/1024/1024;

        $podcast=Podcast::create($data);
        if ($request->has('tags')){
            $podcast->tags()->attach($request->tags);
        }
        
        if ($podcast){
            return ApiResponse::sendResponse(200,'your Podcast Created Successfully',new PodcastResource($podcast));
        }
        return ApiResponse::sendResponse(200,'your Podcast Not Created Successfully',[]);
    }
    public function showPodcast($id)
    {
        $podcast=Podcast::findorFail($id);
        if ($podcast){
            return ApiResponse::sendResponse(200,'Podcast Retrieved Successfully',new PodcastResource($podcast));
        }
        return ApiResponse::sendResponse(200,'Podcast Not Retrieved Successfully',[]);
    }
    public function showChannelPodcasts($channel_id)
    {
        $podcasts=Podcast::where('channel_id',$channel_id)->where('approved',1)->get();
        if (count($podcasts)>0){
            return ApiResponse::sendResponse(200,'Channel Podcasts Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,' No Channel Podcasts ',[]);
    }

    public function download($id)
    {
        $podcastname=Podcast::findorFail($id)->pluck('podcast');
        $podcastname=$podcastname[0];

        $user = auth()->user();
        $user->download()->attach($id);
        return ApiResponse::sendResponse(200,'podcast Was Added to Download List Successfully',[]);

    }
    public function userDownloads()
    {
        $user = auth()->user();
//        return $user->download;
        $downloadedPodcasts=Download::where('user_id',$user->id)->pluck('podcast_id');
        $podcasts=Podcast::whereIn('id', $downloadedPodcasts)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,"User Downloaded Podcasts Retrieved Successfully",
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,"User Downloaded Podcasts Not Retrieved Successfully",[]);

    }
    public function latest()
    {
        $latestPodcast=Podcast::where('approved',1)->latest()->take(5)->get();
        if ($latestPodcast){
            return ApiResponse::sendResponse(200,'Latest Podcasts Retrieved Successfully',
                PodcastResource::collection($latestPodcast));
        }
        return ApiResponse::sendResponse(200,'Latest Podcasts Retrieved Successfully',[]);
    }
    public function delete($podcast_id)
    {
        $channelId= Channel::where('user_id',auth()->user()->id)->pluck('id');
        $channelId=$channelId[0];
        $podcast_channel_id=DB::table('podcasts')->where('id',$podcast_id)->pluck('channel_id');
        $podcast_channel_id=$podcast_channel_id[0];
        #------------
        $oldPath=DB::table('podcasts')->where('id',$podcast_id)->pluck('podcast');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #----------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);

        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #------------
        if ($channelId==$podcast_channel_id){
            DB::table('podcasts')->where('id',$podcast_id)->delete();
        }
        return ApiResponse::sendResponse(200,'Podcast Deleted Successfully',[]);

    }
    public function podcastTags($podcast_id)
    {
        $tagsIds=PodcastTag::where('podcast_id',$podcast_id)->pluck('tag_id');
        $tags=Tag::whereIn('id',$tagsIds)->get();
        if ($tags){
            return ApiResponse::sendResponse(200,'Podcast Tags Retrieved Successfully',
                TagResource::collection($tags));
        }
        return ApiResponse::sendResponse(200,'Podcast Tags Not Retrieved Successfully',[]);
    }
    public function tagPodcasts($tag_id)
    {
        $podcastsIds=PodcastTag::where('tag_id',$tag_id)->pluck('podcast_id');
        $podcasts=Podcast::whereIn('id',$podcastsIds)->where('approved',1)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,'Podcast Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Podcast Not Retrieved Successfully', []);
    }
    public function unapproved()
    {
        $podcasts=Podcast::where('approved',0)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,'Unapproved Podcasts Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Unapproved Podcasts Not Retrieved Successfully',[]);
    }
    public function approve($podcast_id)
    {
        DB::table('podcasts')->where('id',$podcast_id)->update([
            'approved'=>1,
        ]);
        $channel_id=DB::table('podcasts')->where('id',$podcast_id)->pluck('channel_id');
        $channel_id=$channel_id[0];
        DB::table('channels')->where('id',$channel_id)->update([
            'active'=>1,
        ]);
        //
        $podcast=Podcast::where('id',$podcast_id)->first();
        $followers=DB::table('channel_user')->where('channel_id',$channel_id)
            ->pluck('user_id');
        $users=User::whereIn('id',$followers)->get();
        $channel_name= Channel::where('id',$channel_id)->pluck('name');
        $channel_name=$channel_name[0];
        $channel_image= Channel::where('id',$channel_id)->pluck('image');
        $channel_image=$channel_image[0];
        Notification::send($users,new CreatePodcast($podcast->id,$podcast->title,$channel_name,$channel_image));
      //
        ##----------
//        $usersToken=User::whereIn('id',$followers)->get()->pluck('token');
//       $firebase = (new Factory)
//           ->withServiceAccount(__DIR__.'/../../config/firebase_credentials.json');
//
//       $messaging = $firebase->createMessaging();
//
//       $message = CloudMessage::fromArray([
//           'notification' => [
//
//           'podcast_id'=>$podcast->id,
//           'podcast_title'=>$podcast->title,
//           'channel_name'=>$channel_name,
//           'channel_image'=>$channel_image,
//           ],
//           'token' => $usersToken
//       ]);
//
//       $messaging->sendMulticast($message);

        ##---------
        return ApiResponse::sendResponse(200,'Podcast Approved Successfully And Channel Activated Successfully',[]);
    }

    public function adminDelete($podcast_id)
    {
        $oldPath=DB::table('podcasts')->where('id',$podcast_id)->pluck('podcast');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #----------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);

        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #------------

        DB::table('podcasts')->where('id',$podcast_id)->delete();

        return ApiResponse::sendResponse(200,'Podcast Deleted Successfully',[]);
    }
    public function report($podcast_id)
    {
        DB::table('podcasts')->where('id',$podcast_id)->update([
           'reported'=>1,
        ]);
        return ApiResponse::sendResponse(200,'Podcast Reported Successfully',[]);
    }
    public function reportedPodcasts()
    {
        $podcasts=Podcast::where('reported',1)->get();
        if ($podcasts){
            return ApiResponse::sendResponse(200,'Reported Podcasts Retrieved Successfully',
                PodcastResource::collection($podcasts));
        }
        return ApiResponse::sendResponse(200,'Reported Podcasts Not Retrieved Successfully',[]);


    }



}
