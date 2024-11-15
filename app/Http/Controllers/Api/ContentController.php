<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentResource;
use App\Http\Resources\ContentUserResource;
use App\Models\Content;
use App\Models\ContentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    public function createContents(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name' => 'array|required|unique:contents,name',
            'name.*' => 'string|distinct',
        ],[],[]);

        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Insert Validation Error',$validator->errors());
        }
        foreach ($request->name as $k => $v){
            Content::create(['name'=>$v]);
        }
        return ApiResponse::sendResponse(200,'Contents Inserted Successfully',[]);
    }
    public function showContents()
    {
        $contents=Content::all();
        if (count($contents)>0){
            return ApiResponse::sendResponse(200,'Contents Retrieved Successfully',ContentResource::collection($contents));
        }
        return ApiResponse::sendResponse(200,'Contents Not Retrieved Successfully',[]);
    }
    public function selectUserContents(Request $request)
    {
        $user = auth()->user();

        $user->contents()->attach($request->contentsIds);
        $userContents=ContentUser::where('user_id',$user->id)->get();

        return ApiResponse::sendResponse(200,'User Contents Inserted Successfully',
            ContentUserResource::collection($userContents));
    }
}
