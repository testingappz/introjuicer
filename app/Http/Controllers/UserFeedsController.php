<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class UserFeedsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user_feeds(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'limit' => 'required'
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error  = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $user_id = $request->user_id;
                $connectedOrNot = DB::table('connected_users')
                        ->where('status', '1')
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('sender_user_id', $user_id)
                                  ->orwhere('receiver_user_id',$user_id);
                        })
                        ->get()
                        ->toArray();
                foreach ($connectedOrNot as $key => $value) {
                   if($value->sender_user_id == $user_id){
                       $ids[] = $value->receiver_user_id;
                   }
                   elseif($value->receiver_user_id == $user_id){
                       $ids[] = $value->sender_user_id;
                   }
                }
                // print_r($ids); die;
                if(!empty($ids)){
                    $id = "(";
                    $count = count($ids);
                    foreach($ids as $key => $value){
                        if(++$key == $count) $id .= "user_feeds.user_id like '%$value%')";
                        else $id .= "user_feeds.user_id like '%$value%' or ";
                    }
                }
                
                $limit = $request->limit;
                $skip = ($limit - 1) * 20;
                $group_ids_array = $request->group_id;
                if(!empty($id)){
                    $data = DB::select("SELECT user_feeds.user_id,message_type,message,users.name AS user_name ,user_feeds.id as feedId
                    FROM user_feeds JOIN users 
                    ON users.id = user_feeds.user_id
                    WHERE $id or (user_feeds.user_id  = $user_id)
                    ORDER BY user_feeds.id DESC");
                }
                else {
                    $data = DB::select("SELECT user_feeds.user_id,message_type,message,users.name AS user_name ,user_feeds.id as feedId
                    FROM user_feeds JOIN users 
                    ON users.id = user_feeds.user_id
                    WHERE user_feeds.user_id  = $user_id
                    ORDER BY user_feeds.id DESC");
                }
                // $data = DB::table("user_feeds")
                //             // ->where("user_id",$user_id)
                //             ->Join('users', 'users.id', '=', 'user_feeds.user_id')
                //             ->orderBy('user_feeds.id','desc')
                //             // ->skip($skip)
                //             // ->take(20)
                //             ->get(['user_feeds.user_id','message_type','message','users.name AS user_name','user_feeds.id as feedId'])
                //             ->toArray();
                    if(!empty($data)){
                        foreach ($data as $key => $value) {
                            //current user liked or not this feed
                            $liked = DB::table('user_feeds_likes')->where('feed_id',$value->feedId)->where('user_id',$user_id)->count();
                            if($liked == 1) $value->liked = "true";
                            else $value->liked = "false";
                            if ($value->message_type == "image") {
                                $value->message = url('/uploads/user_feeds_images').'/'.$value->message;
                            }
                            if($value->message_type == "image/caption"){
                                $explodedArray = explode('`',$value->message);
                                $value->message =  url('/uploads/user_feeds_images').'/'.$explodedArray[0];
                                $value->caption = json_decode($explodedArray[1]);
                                $value->message_type = "image";
                            }
                            else {
                                $value->caption = "";
                            }
                            if($value->message_type == "text")
                            $value->message = json_decode($value->message);
                            $path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                            // print_r($path);
                            if (is_dir($path)) $imagePathArray = scandir($path);
                            else $imagePathArray = [];
                            // print_r($imagePathArray);
                            if (!empty($imagePathArray)) {
                                $image_url =  url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$imagePathArray['2'];
                                $value->user_image = $image_url;
                            }
                            else $value->user_image = [];
                        }
                    }
                    else $data = [];
                    $pendingUserConnections = DB::table('connected_users')
                                ->where('status', '0')
                                ->Where('receiver_user_id', $user_id)
                                ->get(['sender_user_id as user_id'])
                                ->toArray();
                               
                    if(!empty($pendingUserConnections)){
                        foreach ($pendingUserConnections as $key => $value) {
                            $path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                            if (is_dir($path)) $imagePathArray = scandir($path);
                            else $imagePathArray = [];
                            if (!empty($imagePathArray)) {
                                $image_url =  url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$imagePathArray['2'];
                                $value->user_image = $image_url;
                            }
                            else $value->user_image = [];
                            $value->user_name = DB::table('users')->where("id",$value->user_id)->pluck('name')->first();
                            // Logged in user image
                            $loggedinpath = public_path()."/uploads/user_profile_images".'/'.$user_id;
                            if (is_dir($loggedinpath)) $loginImagePathArray = scandir($loggedinpath);
                            else $loginImagePathArray = [];
                            if (!empty($loginImagePathArray)) {
                                $loginImageUrl =  url('/uploads/user_profile_images').'/'.$user_id.'/'.$loginImagePathArray['2'];
                                $value->logged_in_user_image = $loginImageUrl;
                            }
                            else $value->logged_in_user_image = [];
                            $value->type = "User_to_user";
                        }
                    }
                    else $pendingUserConnections = [];
                    $pendingGroupConnections = DB::table('group_users')
                            ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                            ->where('group_users.user_id',$user_id)
                            ->where('group_users.user_type',1)
                            ->where('group_users.user_status',0)
                            ->get(['heading','groups.id as group_id','group_users.user_id'])
                            ->toArray();
                    if(!empty($pendingGroupConnections)){
                        foreach ($pendingGroupConnections as $key => $value) {
                            $imagePath = DB::table('images')->where("group_id",$value->group_id)->pluck('image_path')->first();
                            $value->group_image = url('/uploads/group_images').'/'.$value->group_id.'/'.$imagePath;
                            $user_images = url('/uploads/user_profile_images').'/'.$value->user_id;
                                $path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                                if (is_dir($path)) $imagePathArray = scandir($path);
                                else $imagePathArray = [];
                                if (!empty($imagePathArray)){
                                    $value->userimage = $user_images.'/'.$imagePathArray[2];
                                }
                                else $value->userimage = " ";
                            $value->type = "Group_to_user";
                            $value->group_type = DB::table('groups')->where("id",$value->group_id)->pluck('type')->first();
                        }
                    }
                    else $pendingGroupConnections = [];
                    if(!empty($group_ids_array)){
                        $group_ids = explode(",", $group_ids_array);
                        foreach ($group_ids as $key => $value) {
                            $userRequest = DB::table('group_users')
                                    ->where('group_id',$value)
                                    ->where('user_status',0)
                                    ->Where('user_type', 2)
                                    ->get(['user_id','group_id'])
                                    ->toArray();
                            foreach ($userRequest as $key1 => $value1) {
                                $test[] = $value1;
                            }
                        }
                        if(!empty($test)) {
                            foreach($test as $key => $value){
                                $groupHeading = DB::table('groups')->where('id',$value->group_id)->pluck('heading')->first();
                                $userName = DB::table('users')->where('id',$value->user_id)->pluck('name')->first();
                                $value->groupName = $groupHeading;
                                $value->userName = $userName;
                                $user_images = url('/uploads/user_profile_images').'/'.$value->user_id;
                                $path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                                if (is_dir($path)) $imagePathArray = scandir($path);
                                else $imagePathArray = [];
                                if (!empty($imagePathArray)){
                                    $value->userimage = $user_images.'/'.$imagePathArray[2];
                                }
                                else $value->userimage = " ";
                                $imagePath = DB::table('images')->where("group_id",$value->group_id)->pluck('image_path')->first();
                                $value->group_image = url('/uploads/group_images').'/'.$value->group_id.'/'.$imagePath;
                                $value->type = "User_to_group";
                            }
                        }
                        else $test = [];
                    }
                    else $test = [];
                    $responseArray = [];
                    $responseArray = array_merge($data,$test,$pendingGroupConnections,$pendingUserConnections);
                    
                    $responeResult = array_slice( $responseArray, $skip, 20 );
                    $countRecords = count($responseArray);
                    $pages = ceil($countRecords / 20);
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $responeResult;
                $response['total_records'] = $pages;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function add_user_feeds(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'message_type' => 'required',
                'message' => 'required',
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error  = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $user_id = $request->user_id;
                $message_type = $request->message_type;
                $message = $request->message;
                $caption = $request->caption;
                if($message_type == "text"){
                    $message = json_encode($message);
                    DB::table('user_feeds')
                        ->insert(['user_id' => $user_id,'message_type'=>$message_type,'message'=>$message]);
                $responseArray['message_type'] = "text";
                $responseArray['message'] = json_decode($message);
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $responseArray;
                return $response;
                }
                elseif ($message_type == "image"){
                    if(!empty($caption)){
                        $file = $request->file('message');
                        $imageName = $file->getClientOriginalName();
                            $imagePath = public_path('uploads/user_feeds_images').'/'; 
                            $file->move($imagePath, $imageName);
                            $imageNameWithCaption = $imageName.'`'.json_encode($caption); 
                            DB::table('user_feeds')->insert(['user_id' => $user_id,'message_type'=>"image/caption",'message'=>$imageNameWithCaption]);
                            $responseArray['message_type'] = "image";
                            $responseArray['message'] = url('/uploads/user_feeds_images').'/'.$imageName;
                            $response['status'] = "Okay";
                            $response['error'] = "0";
                            $response['data'] = $responseArray;
                            return $response;
                    }
                    else {
                        $file = $request->file('message');
                        // Store group images in folder according to user id
                        // foreach($file as $key => $value){
                            $imageName = $file->getClientOriginalName();
                            $imagePath = public_path('uploads/user_feeds_images').'/'; 
                            $file->move($imagePath, $imageName);
                        // }
                        DB::table('user_feeds')
                            ->insert(['user_id' => $user_id,'message_type'=>$message_type,'message'=>$imageName]);
                            $responseArray['message_type'] = "image";
                            $responseArray['message'] = url('/uploads/user_feeds_images').'/'.$imageName;
                            $response['status'] = "Okay";
                            $response['error'] = "0";
                            $response['data'] = $responseArray;
                            return $response;
                    }
                }
            }
        }
        catch(Exception $e){}
    }
    public function like_feeds(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'feed_id' => 'required|numeric'
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error  = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $user_id = $request->user_id;
                $feed_id = $request->feed_id;
                $alreadyLiked = DB::table('user_feeds_likes')->where('user_id',$user_id)->where('feed_id',$feed_id)->count();
                if($alreadyLiked == 1)
                    DB::table('user_feeds_likes')->where('user_id',$user_id)->where('feed_id',$feed_id)->delete();
                else
                    DB::table('user_feeds_likes')->insert(['user_id' => $user_id,'feed_id'=>$feed_id,'likes'=>1]);
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "like added";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function add_favorite(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'ids' => 'required|numeric',
                'type' => 'required'
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error  = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $user_id = $request->user_id;
                $ids = $request->ids;
                $type = $request->type;
                $source = $request->source;
                $alreadyFavorite = DB::table('user_favorites')->where('ids',$ids)->where('user_id',$user_id)->where('type',$type)->count();
                if($alreadyFavorite == 1)
                    DB::table('user_favorites')->where('ids',$ids)->where('user_id',$user_id)->where('type',$type)->delete();
                else
                    DB::table('user_favorites')->insert(['ids' => $ids,'user_id'=>$user_id,'type'=>$type,'source'=>$source]);
                
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "Favorite added";
                return $response;
            }
        }
        catch(Exception $e){}
    }
}
