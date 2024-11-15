<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\TokenRequest;
use App\Http\Resources\ChannelResource;
use App\Http\Resources\UserResource;
use App\Models\Channel;
use App\Models\ChannelUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Spatie\FlareClient\Api;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name'=>['required','string','max:255'],
            'email'=>['required','email','max:255','unique:'.User::class],
            'password'=>['required','confirmed',Rules\Password::default()],
        ],[],[]);
        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Regiser Validation Error',$validator->errors());
        }
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);
        $data['token']=$user->createToken('podcastProject')->plainTextToken;
        $data['name']=$user->name;
        $data['email']=$user->email;
        return  ApiResponse::sendResponse(200,'User Account Created Successfully',$data);
    }
    public function login(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email'=>['required','email','max:255'],
            'password'=>['required'],
        ],[],[
            'email'=>'Email',
            'password'=>'Password',
        ]);
        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Login Validation Error',$validator->errors());
        }
        if (Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            $user=Auth::user();
            $data['token']=$user->createToken('podcastProject')->plainTextToken;
            $data['name']=$user->name;
            $data['email']=$user->email;

            return  ApiResponse::sendResponse(200,'User Logged In Successfully',$data);
        }else{
            return  ApiResponse::sendResponse(401,'User Credentials Doesnt exist',[]);
        }
    }
    public function admin_login(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name'=>['required'],
            'password'=>['required'],
        ],[],[
            'name'=>'Name',
            'password'=>'Password',
        ]);
        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Login Validation Error',$validator->errors());
        }
        if ($request->name=="admin" && $request->password=="admin"){
            $user=User::where('name','admin')->first();
            $data['token']=$user->createToken('podcastProject')->plainTextToken;
            $data['name']=$user->name;
//            $data['password']=$user->password;

            return  ApiResponse::sendResponse(200,'Admin Logged In Successfully',$data);
        }else{
            return  ApiResponse::sendResponse(401,'Admin Credentials Doesnt exist',[]);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200,'Logged Out Successfully',[]);
    }
    public function toggleFollowChannel($channel_id)
    {
        $user = auth()->user();

        if ($user->channels()->where('channel_id',$channel_id)->exists()){
            $user->channels()->detach($channel_id);
            $message='Channel Removed From Follow Successfully';
        }else{
            $user->channels()->attach($channel_id);
            $message='Channel Added To Follow Successfully';
        }
        return ApiResponse::sendResponse(200,$message,[]);
    }
    public function userFollowedChannels()
    {
        $user = auth()->user();
        $channelsIds=ChannelUser::where('user_id',$user->id)->pluck('channel_id');
        $channels=Channel::whereIn('id',$channelsIds)->get();
        if ($channels){
            return ApiResponse::sendResponse(200,'User Followed Channels Retrieved Successfully',
                ChannelResource::collection($channels));
        }
        return ApiResponse::sendResponse(200,'User Followed Channels Not Retrieved Successfully',[]);
    }
    public function profile()
    {
        $user= auth()->user();
        return ApiResponse::sendResponse(200,'User profile',new UserResource($user));
    }
    public function setToken(TokenRequest $request)
    {
        $data=$request->validated();
        DB::table('users')->where('id',$request->id)->update([
            'token'=>$request->token,
        ]);
        return ApiResponse::sendResponse(200,'Token Recieved Successfully',[]);
    }

}
