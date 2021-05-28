<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ChatCOntroller2;
use DB;
use App\Model\Group;
class ConnectController extends Controller
{
    public function join_interest(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'interest' => 'required'
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
                $interest = $request->interest;
                $data = DB::table('user_interests')
                    ->where('user_id',$user_id)
                    ->where('interest_type',$interest)
                    ->first();
                if(empty($data)){
                    DB::table('user_interests')->insert(['user_id'=>$user_id,'interest_type'=>$interest]);
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "You have successfully joined this interest.";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function friend_requests(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
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
                $groupsRequest = DB::table('group_users')
                            ->where('user_id',$user_id)
                            ->where('user_status',0)
                            ->Where(function ($query)  {
                                $query->orwhere('user_type', "0")
                                    ->orwhere('user_type', "1");
                            })
                            ->get(['user_id','group_id'])
                            ->toArray();
                if(!empty($groupsRequest)) {
                    foreach($groupsRequest as $key => $value){
                        $group[] = DB::table('groups')->where('id',$value->group_id)->first(['id','heading AS name']);
                        //$group[] = Group::with(['groupImages'])->where('id',$value->group_id)->first(['id','heading AS name'])->toArray();
                    }
                }else{
                    $group =[];
                }
                    if(!empty($group)){
                        foreach ($group as $key => $value) {
                            $test = DB::table('images')->where('group_id',$value->id)->get(['image_path'])->toArray();
                            if(!empty($test)){
                                foreach ($test as $key1 => $value1) {
                                    $value->group_images[] = url('/uploads/group_images').'/'.$value->id.'/'.$value1->image_path;
                                }
                            }
                            else $value->group_images = [];
                        }
                    }
                $data = DB::table('connected_users')
                            ->where('receiver_user_id',$user_id)
                            ->where('status',0)
                            ->get()
                            ->toArray();
                foreach($data as $key => $value){
                    $memberDetails[] = DB::table('users')->where('id', $value->sender_user_id)->select('id','name')->first();
                }
                if (!empty($memberDetails)) {
                    foreach($memberDetails as $key => $value){
                        //Get User images from folder
                        $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->id;
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->id;

                        if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                        else $imagePathArray = [];

                        if (!empty($imagePathArray)) {
                            $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                        }
                        else{
                            $value->user_image = " ";
                        }

                        // foreach(glob($user_images_root_path.'/*.*') as $image_root_path) {
                        //     $imagesPath = $image_root_path;
                        // }
                        // if(isset($imagesPath)){
                        //     $var = preg_split("#/#", $imagesPath);
                        //     // print_r($var);
                        //     //This check is for my local system public path location
                        //     if(!isset($var[10])){
                        //         $var[10] = $var[4];
                        //     }
                        //     $image_url = $user_images_display_path.'/'.$var[10];
                        //     $value->user_image = $image_url;
                        //     unset($imagesPath);
                        // }
                        // else{
                        //     $value->user_image = " ";
                        // }
                        //Get User images from folder Code end here
                    }
                }
                if(empty($memberDetails)){
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $group;
                    return $response;
                }
                elseif (empty($group)) {
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $memberDetails;
                    return $response;
                }
                else{
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = array_merge($memberDetails,$group);
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function manage_requests(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'receive_user_id' => 'required|numeric',
                'type' => 'required',
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
                $receive_user_id = $request->receive_user_id;
                $type = $request->type;
                if($type == "accept"){
                    $true = DB::table('connected_users')
                    ->where('sender_user_id',$user_id)
                    ->where('receiver_user_id',$receive_user_id)
                    ->update(['status'=>1]);
                    if(0 == $true){
                        DB::table('connected_users')
                        ->where('sender_user_id',$receive_user_id)
                        ->where('receiver_user_id',$user_id)
                        ->update(['status'=>1]);
                    }
                    $response['status'] = "You have successfully connected with each other.";
                    $response['error'] = "0";
                    $response['data'] = "";
                    return $response;
                }
                else{
                    $test = DB::table('connected_users')
                    ->where('sender_user_id',$user_id)
                    ->where('receiver_user_id',$receive_user_id)
                    ->delete();
                    if(0 == $test){
                        DB::table('connected_users')
                        ->where('sender_user_id',$receive_user_id)
                        ->where('receiver_user_id',$user_id)
                        ->delete();
                    }
                    $response['status'] = "You have deleted the friend request.";
                    $response['error'] = "0";
                    $response['data'] = "";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function block_user(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'block_userid' => 'required|numeric',
                'blocked_userid' => 'required|numeric'
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
                $block_userid = $request->block_userid;
                $blocked_userid = $request->blocked_userid;
                $alreadyConnected = DB::table('connected_users')
                                    ->where('sender_user_id',$block_userid)
                                    ->where('receiver_user_id',$blocked_userid)
                                    // ->Where(function ($query) use($block_userid) {
                                    //     $query->orwhere('sender_user_id', $block_userid)
                                    //           ->orwhere('receiver_user_id',$block_userid);
                                    // })
                                    // ->Where(function ($query) use($blocked_userid) {
                                    //     $query->orwhere('sender_user_id', $blocked_userid)
                                    //           ->orwhere('receiver_user_id',$blocked_userid);
                                    // })
                                    ->first();
                $alreadyConnected1 = DB::table('connected_users')
                                    ->where('sender_user_id',$blocked_userid)
                                    ->where('receiver_user_id',$block_userid)
                                    ->first();
                // print_r($alreadyConnected); print_r($alreadyConnected1); die;
                if(!empty($alreadyConnected)){
                    DB::table('connected_users')->where('sender_user_id',$block_userid)->where('receiver_user_id',$blocked_userid)->update(['status'=>2]);
                }
                elseif(!empty($alreadyConnected1)){
                    DB::table('connected_users')->where('sender_user_id',$blocked_userid)->where('receiver_user_id',$block_userid)->update(['sender_user_id'=>$block_userid,'receiver_user_id'=>$blocked_userid,'status'=>2]);
                }
                else{
                    DB::table('connected_users')->insert(['sender_user_id'=>$block_userid,'receiver_user_id'=>$blocked_userid,'status'=>2]);
                }
                $response['status'] = "You have successfully blocked user.";
                $response['error'] = "1";
                $response['data'] = "";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function list_connected_user(Request $request){
        $response = [];
        try{
            $user_id = $request->user_id;
            $group_id = $request->group_id;
            $data = DB::table('connected_users')
                        ->where('status', '1')
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('sender_user_id', $user_id)
                                  ->orwhere('receiver_user_id',$user_id);
                        })
                        ->get(['connected_users.sender_user_id AS send_user_id','connected_users.receiver_user_id AS receive_user_id'])
                        ->toArray();
            foreach($data as $key => $value){
                if($value->send_user_id == $user_id){
                    $userNames[] = DB::table('users')
                    ->where('users.id', $value->receive_user_id)
                    ->select('users.id','name')
                    ->first();
                }
                elseif($value->receive_user_id == $user_id){
                    $userNames[] = DB::table('users')
                    ->where('users.id', $value->send_user_id)
                    ->select('users.id','name')
                    ->first();
                }
            }
            // print_r($userNames); die;
            if(!isset($userNames)){
                $userNames = [] ;
            }
            else{
                foreach($userNames as $key => $value){
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->id;
                    $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->id;
                    if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                        else $imagePathArray = [];

                        if (!empty($imagePathArray)) {
                            $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                        }
                        else{
                            $value->user_image = [];
                        }
                    // foreach(glob($user_images_root_path.'/*.*') as $image_root_path) {
                    //     $imagesPath = $image_root_path;
                    // }
                    // if(isset($imagesPath)){
                    //         $var = preg_split("#/#", $imagesPath);
                    //         //This check is for my local system public path location
                    //         if(!isset($var[10])){
                    //             $var[10] = $var[4];
                    //         }
                    //         $image_url = $user_images_display_path.'/'.$var[10];
                    //         $value->user_image = $image_url;
                    //     unset($imagesPath);
                    // }
                    // else{
                    //     $value->user_image = [];
                    // }
                    $status = DB::table('group_users')
                            ->where('user_id', $value->id)
                            ->where('group_id',$group_id)
                            ->first();
                    if(!empty($status)) $value->request_status = "1";
                    else $value->request_status = "0";
                }
            }
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = $userNames;
            return $response;
        }
        catch(Exception $e){}
    }
    public function send_invite(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'group_id' => 'required|numeric'
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
                $group_id = $request->group_id;
                $groupSize = DB::table('groups')->where('id',$group_id)->pluck('size')->first();
                if($groupSize >= 1500){
                    $groupSize ++;
                    DB::table('groups')->where('id',$group_id)->update(['size'=>$groupSize]);
                }
                $memebersInGroup = DB::table('group_users')->where('group_id',$group_id)->count();
                if($memebersInGroup < $groupSize){


                    $true = DB::table('group_users')->insert(['user_id'=>$user_id,'group_id'=>$group_id,'user_status'=>0,'user_type'=>1]);
                    // Send push notification to other user who got friend request
                    $returnData = new ChatCOntroller2();
                    $group_name = DB::table('groups')->where('id',$group_id)->pluck('heading')->first();
                    $data = [];
                    $data['title'] = "You have a connection request";
                    $data['desc'] = "You have a connection request from ".$group_name;
                    $device_token = DB::table('device_token')->where('user_id',$user_id)->orderBy('id', 'DESC')->pluck('device_token')->first();
                    if(!empty($device_token))
                    $returnData->iOS($data, $device_token, $group_id,'groupRequestReceived');
                    // end here
                    if(true == $true){
                        $response['status'] = "Invitation sent.";
                        $response['error'] = "0";
                        $response['data'] = " ";
                        return $response;
                    }
                }
                else {
                    $response['status'] = "Group reaches the maximum number of users.";
                    $response['error'] = "1";
                    $response['data'] = "Group reaches the maximum number of users.";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function view_sent_friend_requests(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'receiver_id' => 'required|numeric',
                'group' => 'required',
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $group = $request->group;
                $sender_user_id = $request->user_id;
                $receiver_id = $request->receiver_id;
                if($group == "true") {
                    $connectedGroups = DB::table('group_users')
                            ->where('user_id', $sender_user_id)
                            ->where('group_id', $receiver_id)
                            ->where('user_status', 1)
                            ->first();
                    $pendingroupRequests = DB::table('group_users')
                            ->where('user_id', $sender_user_id)
                            ->where('group_id', $receiver_id)
                            ->where('user_status', 0)
                            ->first();
                    $notConnectedGroups = DB::table('group_users')
                            ->where('user_id', $sender_user_id)
                            ->where('group_id', $receiver_id)
                            ->first();
                    if (!empty($connectedGroups)) {
                        $response['status'] = "You are already connected to this group.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                    else if(!empty($pendingroupRequests)){
                        $response['status'] = "Your request is pending.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                    else{
                        $response['status'] = "Your are not connected to this group.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                }
                else{
                    $connectedUsers = DB::table('connected_users')
                            // ->where('sender_user_id', $sender_user_id)
                            ->Where(function ($query) use($sender_user_id) {
                                $query->orwhere('sender_user_id', $sender_user_id)
                                      ->orwhere('receiver_user_id',$sender_user_id);
                            })
                            // ->where('receiver_user_id', $receiver_id)
                            ->Where(function ($query) use($receiver_id) {
                                $query->orwhere('sender_user_id', $receiver_id)
                                      ->orwhere('receiver_user_id',$receiver_id);
                            })
                            ->where('status', 1)
                            ->first();
                    $PendingRequests = DB::table('connected_users')
                            // ->where('sender_user_id', $sender_user_id)
                            ->Where(function ($query) use($sender_user_id) {
                                $query->orwhere('sender_user_id', $sender_user_id)
                                      ->orwhere('receiver_user_id',$sender_user_id);
                            })
                            // ->where('receiver_user_id', $receiver_id)
                            ->Where(function ($query) use($receiver_id) {
                                $query->orwhere('sender_user_id', $receiver_id)
                                      ->orwhere('receiver_user_id',$receiver_id);
                            })
                            ->where('status', 0)
                            ->first();
                    if (!empty($connectedUsers)) {
                        $response['status'] = "You are already connected to this user.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                    else if(!empty($PendingRequests)) {
                        $response['status'] = "Your request is pending.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                    else{
                        $response['status'] = "Your are not connected to each other.";
                        $response['error'] = "0";
                        $response['data'] = [];
                        return $response;
                    }
                }
            }
        }
        catch(Exception $e){}
    }
    public function group_friend_requests(Request $request){
        $response =[];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'group_id' => 'required'
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = " ";
                return $response;
            }
            else{
                $user_id = $request->user_id;
                $group_ids_array = $request->group_id;
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
                            foreach($imagePathArray as $key1 => $value1) {
                                if ($key1 == 0 || $key1 == 1) continue;
                                $value->userimage = $user_images.'/'.$value1;
                            }
                        }
                        else $value->userimage = " ";
                    }
                }
                else $test = [];
                // print_r($userRequest);
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $test;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function group_ids(Request $request){
        $response = [];
        try{
            $user_id = $request->user_id;
            $groupIds = DB::table('group_users')
                        ->where('user_id',$user_id)
                        ->Where('user_type', 0)
                        ->pluck('group_id')
                        ->toArray();
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = $groupIds;
            return $response;
        }
        catch(Exception $e){}
    }
    public function manage_group_requests(Request $request){
         $response = [];
         try {
             $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'group_id' => 'required|numeric',
                'type' => 'required',
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
                $group_id = $request->group_id;
                $type = $request->type;
                if($type == "accept"){
                    $true = DB::table('group_users')
                    ->where('user_id',$user_id)
                    ->where('group_id',$group_id)
                    ->update(['user_status'=>1]);
                    // Send push notification to other user who accept group join request
                    $group_type = DB::table('groups')->where('id',$group_id)->pluck('type')->first();
                    $returnData = new ChatCOntroller2();
                    $group_name = DB::table('groups')->where('id',$group_id)->pluck('heading')->first();
                    $user_name = DB::table('users')->where('id',$user_id)->pluck('name')->first();
                    $data = [];
                    $data['title'] = "Group request accepted";
                    $data['desc'] = $user_name." has joined ".$group_name;
                    // $device_token = "216548f15201ad0d3e6328207c7e8ce8aab0eab97ccdb89d728758fa8b7d37de";
                    $device_token = DB::table('device_token')->where('user_id',$user_id)->orderBy('id', 'DESC')->pluck('device_token')->first();
                    if(!empty($device_token))
                    $returnData->iOS($data, $device_token, $group_id,'groupRequestAccepted',$group_type);
                    // end here
                    $response['status'] = "You have successfully joined this group.";
                    $response['error'] = "0";
                    $response['data'] = "";
                    return $response;
                }
                else{
                    $test = DB::table('group_users')
                    ->where('user_id',$user_id)
                    ->where('group_id',$group_id)
                    ->delete();
                    $response['status'] = "Group request cancel successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "";
                    return $response;
                }
            }
         }
         catch (Exception $e){}
    }
    public function get_location(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'latitude' => 'required',
                'longitude' => 'required'
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
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                    DB::table('users')
                        ->where('id', $user_id)
                        ->update([
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        ]);
                $locArray = array();
                $locArray['latitude'] = $latitude;
                $locArray['longitude'] = $longitude;
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $locArray;
                return $response;
            }
        }
        catch (Exception $e){}
    }
    public function connect_filters(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'gender' => 'required',
                'age' => 'required',
                'distance' => 'required'
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
                $gender = $request->gender;
                $age  = $request->age;
                $religion = $request->religion;
                $looking_for = $request->looking_for;
                $relationship_status = $request->relationship_status;
                $education = $request->education;
                $nationality = $request->nationality;
                $height = $request->height;
                $language = $request->language;
                $trust_rating = $request->trust_rating;
                $distance = $request->distance;
                $interests = $request->interests;
                $location = $request->location;
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $data = DB::table('user_prefrences')->where('user_id', $user_id)->first();
                if(empty($data)){
                    $true = DB::table('user_prefrences')
                    ->insert(['user_id'=>$user_id,'gender' => $gender, 'age' =>$age, 'distance'=>$distance,'religion'=>$religion,'interests'=>$interests,'looking_for'=>$looking_for,'relationship_status'=>$relationship_status,'education'=>$education,'nationality'=>$nationality,'height'=>$height,'language'=>$language,'trust_rating'=>$trust_rating,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude]);

                    $response['status'] = "Prefrences added successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences added successfully. ";
                    return $response;
                }
                else {
                    DB::table('user_prefrences')->where('user_id', $user_id)->update([
                        'gender' => $gender,
                        'age' => $age,
                        'distance' => $distance,
                        'religion' => $religion,
                        'interests'=>$interests,
                        'looking_for'=>$looking_for,
                        'relationship_status'=>$relationship_status,
                        'education'=>$education,
                        'nationality'=>$nationality,
                        'height'=>$height,
                        'language'=>$language,
                        'trust_rating'=>$trust_rating,
                        'location' => $location,
                        'latitude' =>$latitude,
                        'longitude' => $longitude
                        ]);

                    $response['status'] = "Prefrences updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences updated successfully. ";
                    return $response;
                }
            }
        }
        catch (Exception $e){}
    }
    public function view_preferences(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric'
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
                $data = DB::table('user_prefrences')->where('user_id', $user_id)->first();
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch (Exception $e){}
    }
    public function user_prefrences_money(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'distance' => 'required'
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
                $distance = $request->distance;
                $price = $request->price;
                $lower_price = $request->lower_price;
                $data = DB::table('user_prefrences_group_money')->where('user_id', $user_id)->first();
                if(empty($data)){
                    $true = DB::table('user_prefrences_group_money')
                    ->insert(['user_id'=>$user_id,'distance'=>$distance,'lower_price'=>$lower_price,'price'=>$price]);
                    $response['status'] = "Prefrences added successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences added successfully. ";
                    return $response;
                }
                else {
                    DB::table('user_prefrences_group_money')->where('user_id', $user_id)->update([
                        'distance' => $distance,
                        'lower_price' => $lower_price,
                        'price' => $price
                        ]);
                    $response['status'] = "Prefrences updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences updated successfully. ";
                    return $response;
                }
            }
        }
        catch (Exception $e){}
    }
    public function unblock_user(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'block_userid' => 'required|numeric',
                'blocked_userid' => 'required|numeric'
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
                $block_userid = $request->block_userid;
                $blocked_userid = $request->blocked_userid;
                DB::table('connected_users')->where('sender_user_id',$block_userid)->where('receiver_user_id',$blocked_userid)->delete();
                    $response['status'] = "You have successfully unblocked user.";
                    $response['error'] = "1";
                    $response['data'] = "";
                    return $response;
            }
        }
        catch (Exception $e){}
    }
    public function leave_group(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'group_id' => 'required|numeric'
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
                $group_id = $request->group_id;
                DB::table('group_users')
                    ->where('user_id',$user_id)
                    ->where('group_id',$group_id)
                    ->delete();
                $response['status'] = "Group left successfully. ";
                $response['error'] = "0";
                $response['data'] = "Group left successfully. ";
                return $response;
            }
        }
        catch (Exception $e){}
    }
}
