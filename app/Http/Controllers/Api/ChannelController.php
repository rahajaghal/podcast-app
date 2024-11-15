<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChannelRequest;
use App\Http\Requests\ImageRequest;
use App\Http\Resources\ChannelResource;
use App\Http\Resources\ChanneResource;
use App\Models\Channel;
use App\Models\User;
use App\Notifications\CreateChannel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\FlareClient\Api;


class ChannelController extends Controller
{
    public function create(ChannelRequest $request)
    {

        $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();

        $path=$request->file('image')->storeAs('imgs',$image,'imgs');
        $data=$request->validated();
        $data['image']=$path;
        $data['user_id']=$request->user()->id;
        $channel=Channel::create($data);
        if ($channel) return ApiResponse::sendResponse(200,'your Channel Created Successfully',new ChannelResource($channel));
    }
    public function showChannelDescription($channel_id)
    {
        $channelDescription=Channel::find($channel_id)->pluck('description');
        $channelDescription=$channelDescription[0];
        if ($channelDescription){
            return ApiResponse::sendResponse(200,'channel Description Retrieved Successfully',$channelDescription);
        }
        return ApiResponse::sendResponse(200,'Channel Description Not Retrieved Successfully',[]);
    }
    public function channelInfo($channel_id)
    {
        $channel=Channel::find($channel_id);
        if ($channel){
            return ApiResponse::sendResponse(200,'Channel Retrieved Successfully',new ChannelResource($channel));
        }
        return ApiResponse::sendResponse(200,'Channel Not Retrieved Successfully',[]);
    }
    public function updateImage(ImageRequest $request)
    {
        $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();
        $path=$request->file('image')->storeAs('imgs',$image,'imgs');
        $request->validated();
        #----------
        $oldPath=DB::table('channels')->where('id',$request->channel_id)->pluck('image');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);
        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #----------
        DB::table('channels')->where('id',$request->channel_id)->update([
            'image'=>$path,
        ]);
        return ApiResponse::sendResponse(200,'Channel Image Updated Successfully',[]);
    }
    public function notApproved()
    {
        $channels=Channel::where('approved',0)->get();
        if ($channels){
            return ApiResponse::sendResponse(200,'channels not approved Yet Retrieved Successfully',
                ChannelResource::collection($channels));
        }
        return ApiResponse::sendResponse(200,'channels not approved Yet Not Retrieved Successfully',[]);
    }
    public function approve($channel_id)
    {
      DB::table('channels')->where('id',$channel_id)->update([
          'approved'=>1,
      ]);
      $channelUser=Channel::where('id',$channel_id)->pluck('user_id');
      $channelUser=$channelUser[0];

      DB::table('users')->where('id',$channelUser)->update([
            'status'=>1,
      ]);

      return ApiResponse::sendResponse(200,'Channel Approved Successfully',[]);
    }
    public function delete($channel_id)
    {
        $oldPath=DB::table('channels')->where('id',$channel_id)->pluck('image');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #----------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);

        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #------------

        DB::table('channels')->where('id',$channel_id)->delete();

        return ApiResponse::sendResponse(200,'Channel Deleted Successfully',[]);
    }
    public function unActiveChannels()
    {
        $channels=Channel::where('active',0)->get();
        if ($channels){
            return ApiResponse::sendResponse(200,'UnActive Channels Retrieved Successfully',ChannelResource::collection($channels));
        }
        return ApiResponse::sendResponse(200,'UnActive Channels Not Retrieved Successfully',[]);
    }
    public function userChannel()
    {
        $user = auth()->user();

        $channel=Channel::where('user_id',$user->id)->first();
        if ($channel){
            return ApiResponse::sendResponse(200,'User Channel Retrieved Successfully',new ChanneResource($channel));
        }
        return ApiResponse::sendResponse(200,'User Channel Not Retrieved Successfully',null);

    }

}
