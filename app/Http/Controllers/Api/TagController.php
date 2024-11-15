<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{

    public function create(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tag' => 'array|required|unique:tags,tag',
            'tag.*' => 'string|distinct',
        ],[],[]);

        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Insert Validation Error',$validator->errors());
        }
        foreach ($request->tag as $k => $v){
            Tag::create(['tag'=>$v]);
        }
        return ApiResponse::sendResponse(200,'Tags Inserted Successfully',[]);
    }
    public function show()
    {
        $tags=Tag::all();
        if (count($tags)>0){
            return ApiResponse::sendResponse(200,'Tags Retrieved Successfully',TagResource::collection($tags));
        }
        return ApiResponse::sendResponse(200,'Tags Not Retrieved Successfully',[]);
    }
}
