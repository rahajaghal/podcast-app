<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PodcastResource;
use App\Models\Podcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function showUnreadNotifications()
    {

//        return Auth::User()->unreadNotifications->count();
        $notifications=Auth::User()->unreadNotifications;

        return ApiResponse::sendResponse(200,'Notifications Retrieved Successfully',
            NotificationResource::collection($notifications));
//            return ApiResponse::sendResponse(200,'There Is no New Notifications',[]);
    }

    public function showNotification($podcast_id)
    {
        $podcast=Podcast::findorFail($podcast_id);
        $getID =DB::table('notifications')->where('data->podcast_id',$podcast_id)->pluck('id');
        $getID=$getID[0];
        DB::table('notifications')->where('id',$getID)->update(['read_at'=>now()]);

        return ApiResponse::sendResponse(200,'Notification Retrieved Successfully', new PodcastResource($podcast));

    }
}
