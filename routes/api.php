<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\ListenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PodcastController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
##---------------------------------------Authentication MODULE
Route::controller(AuthController::class)->group(function (){
    Route::post('/register','register');
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('auth:sanctum');
    Route::get('/follow/{id}','toggleFollowChannel')->middleware('auth:sanctum');
    Route::get('/show/user/followed/channels','userFollowedChannels')->middleware('auth:sanctum');
    Route::get('/user/profile','profile')->middleware('auth:sanctum');
    Route::get('/sent/token','setToken');
    //
    Route::post('/admin/login','admin_login');
});
##---------------------------------------Podcasts Module
Route::controller(PodcastController::class)->group(function (){
    Route::get('/show/user/home','showHomeUser')->middleware('auth:sanctum');
    Route::get('/user/latest/listened/podcast','latestPodcast')->middleware('auth:sanctum');
    Route::post('/show/podcasts/based/content','showBasedContent');
    Route::post('/show/podcasts/based/content/with/user','showBasedContentWithUser')
        ->middleware('auth:sanctum');

    Route::get('/search/based/title','searchBasedTitle');
    Route::post('/upload','upload')->middleware(['auth:sanctum','check_user']);

    Route::get('/show/podcast/{id}','showPodcast')->middleware('auth:sanctum');
    Route::get('/show/channel/podcasts/{channelID}','showChannelPodcasts');
    Route::get('/podcast/download/{id}','download')->middleware('auth:sanctum');
    Route::get('/user/downloads','userDownloads')->middleware('auth:sanctum');
    Route::get('/show/latest/podcasts','latest');
    Route::get('/delete/podcast/{podcast_id}','delete')->middleware(['auth:sanctum','check_user']);
    Route::get('/show/podcast/tags/{podcast_id}','podcastTags');
    Route::get('/show/podcasts/based/tag/{tag_id}','tagPodcasts');
    Route::get('/show/unapproved/podcasts','unapproved')->middleware(['auth:sanctum','check_admin']);
    Route::get('/approves/podcast/{podcast_id}','approve')->middleware(['auth:sanctum','check_admin']);
    Route::get('/admin/delete/podcast/{podcast_id}','adminDelete')->middleware(['auth:sanctum','check_admin']);
    Route::get('/report/podcast/{podcast_id}','report')->middleware('auth:sanctum');
    Route::get('/show/podcasts/reported','reportedPodcasts')->middleware(['auth:sanctum','check_admin']);
});
Route::controller(ContentController::class)->group(function (){
    Route::post('/create/contents','createContents')->middleware(['auth:sanctum','check_admin']);
    Route::get('/show/all/content','showContents');
    Route::post('/contents/select','selectUserContents')->middleware('auth:sanctum');
});

Route::controller(TagController::class)->group(function (){
    Route::post('/create/tags','create')->middleware(['auth:sanctum','check_user']);
    Route::get('/show/tags','show');
});
Route::controller(NotificationController::class)->group(function (){
    Route::get('/show/unread/notifications','showUnreadNotifications')->middleware('auth:sanctum');
    Route::get('/show/notification/{podcast_id}','showNotification')->middleware('auth:sanctum');
});
Route::controller(FavouriteController::class)->group(function (){
    Route::get('/show/user/favourites/podcasts','favouritePodcasts')->middleware('auth:sanctum');
    Route::get('/user/favourite/podcasts/ids','favouriteIds')->middleware('auth:sanctum');
    Route::get('/toggle/podcast/{podcast_id}','toggleFavourite')->middleware('auth:sanctum');
});
Route::controller(ChannelController::class)->group(function (){
    Route::post('/request/create/channel','create')->middleware('auth:sanctum');
    Route::get('/show/channel/description/{channelID}','showChannelDescription');
    Route::get('/show/channel/info/{channel_id}','channelInfo');
    Route::post('/update/channel/image','updateImage')->middleware(['auth:sanctum','check_user']);
    Route::get('/show/unapproved/channels','notApproved')->middleware(['auth:sanctum','check_admin']);
    Route::get('/approve/channel/{channel_id}','approve')->middleware(['auth:sanctum','check_admin']);
    Route::get('/delete/channel/{channel_id}','delete')->middleware(['auth:sanctum','check_admin']);
    Route::get('/show/un/active/channels','unActiveChannels')->middleware(['auth:sanctum','check_admin']);
    Route::get('/get/user/channel/info','userChannel')->middleware('auth:sanctum');

});
Route::controller(ListenController::class)->group(function (){
   Route::get('/add/podcast/to/user/listens','listenPodcast')->middleware('auth:sanctum');
   Route::put('/update/listen/time','updateUserListenTime')->middleware('auth:sanctum');

   Route::get('/show/top/podcasts','top');
   Route::get('/show/most/listened/podcasts','top');
});
Route::controller(RatingController::class)->group(function (){
    Route::post('/add/review','userReview')->middleware('auth:sanctum');
    Route::get('/show/podcast/comments/{podcast_id}','podcastComments');
});


