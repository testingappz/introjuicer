<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User_favorites;
use DB;

class SettingsController extends Controller
{
    public function user_visibility(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'visibility' => 'required'
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
                $visibility = $request->visibility;
                $connectBeforeMessage = $request->connect_before_message;
                DB::table('users')->where('id',$user_id)->update(['visibility'=>$visibility,'connect_before_message'=>$connectBeforeMessage]);
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = "visibility updated.";
            return $response;
            }
        }
        catch(Exception $e){}
    }
    public function user_activities(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'groups' => 'required',
                'chat' => 'required',
                'relationship_status' => 'required',
                'personal_profile' => 'required',
                'professional_profile' => 'required',
                'introduce_me' => 'required',
                'reconnect_me' => 'required',
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
                $groups = $request->groups;
                $chat = $request->chat;
                $relationship_status = $request->relationship_status;
                $personal_profile = $request->personal_profile;
                $professional_profile = $request->professional_profile;
                $introduce_me = $request->introduce_me;
                $reconnect_me = $request->reconnect_me;
                $existingUser = DB::table('hide_activities')->where('user_id',$user_id)->first();
                if(empty($existingUser)){
                    DB::table('hide_activities')
                            ->insert(['user_id' => $user_id,'groups'=>$groups,'chat'=>$chat,'relationship_status'=>$relationship_status,'personal_profile'=>$personal_profile,'professional_profile'=>$professional_profile,'introduce_me'=>$introduce_me,'reconnect_me'=>$reconnect_me]);
                    $response['status'] = "Settings added successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Settings added successfully. ";
                    return $response;
                }
                else {
                    DB::table('hide_activities')
                        ->where('user_id',$user_id)
                        ->update(['groups'=>$groups,'chat'=>$chat,'relationship_status'=>$relationship_status,'personal_profile'=>$personal_profile,'professional_profile'=>$professional_profile,'introduce_me'=>$introduce_me,'reconnect_me'=>$reconnect_me]);
                    $response['status'] = "Settings  updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Settings  updated successfully. ";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function view_user_activities(Request $request){
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
                $data = DB::table('hide_activities')
                        ->Join('users', 'users.id', '=', 'hide_activities.user_id')
                        ->where('hide_activities.user_id',$user_id)
                        ->first(['hide_activities.*','users.visibility','users.connect_before_message']);
                if(empty($data)) {
                    $data['user_id'] = $user_id;
                    $data['groups'] = 0;
                    $data['chat'] = 0;
                    $data['relationship_status'] = 0;
                    $data['personal_profile'] = 0;
                    $data['professional_profile'] = 0;
                    $data['introduce_me'] =  " ";
                    $data['reconnect_me'] =  " ";
                    $data['visibility'] = DB::table('users')->where('id',$user_id)->pluck('visibility')->first();
                    $data['connect_before_message'] = DB::table('users')->where('id',$user_id)->pluck('connect_before_message')->first();
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function leave_category(Request $request){
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
            else {
                $user_id = $request->user_id;
                $interest = $request->interest;
                $data = DB::table('user_interests')
                    ->where('user_id',$user_id)
                    ->where('interest_type','LIKE',"%$interest%")
                    ->delete();
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "You have successfully left this category.";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function check_connect_before(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric'
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
            else {
                $id = $request->id;
                $data = DB::table('users')
                    ->where('id',$id)
                    ->pluck('connect_before_message')
                    ->first();
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function user_connections(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'checked_user_id' => 'required|numeric'
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
            else {
                $user_id = $request->user_id;
                $checked_user_id = $request->checked_user_id;
                $requestToUser = DB::table('connected_users')
                    // ->where('sender_user_id',$user_id)
                    // ->where('receiver_user_id',$checked_user_id)
                    ->Where(function ($query) use($user_id) {
                        $query->orwhere('sender_user_id', $user_id)
                              ->orwhere('receiver_user_id',$user_id);
                    })
                    ->Where(function ($query) use($checked_user_id) {
                        $query->orwhere('receiver_user_id', $checked_user_id)
                              ->orwhere('sender_user_id',$checked_user_id);
                    })
                    ->first();
                if(empty($requestToUser)) $requestMessage = "You are not connected with each other";
                else if($requestToUser->status == 0) $requestMessage = "Your request is pending.";
                else $requestMessage = "You are connected to each other";

                // Chek secret crush
                $CheckCrush = DB::table('secret_crush')
                    ->where('sender_user_id',$user_id)
                    ->where('receiver_user_id',$checked_user_id)
                    ->first();
                if(empty($CheckCrush)) $crushMessage = False;
                else $crushMessage = TRUE;

                // Check block status
                $blockStatus = DB::table('connected_users')
                    ->where('sender_user_id',$user_id)
                    ->where('receiver_user_id',$checked_user_id)
                    ->where('status',2)
                    ->first();

                if(empty($blockStatus)) $blockMessage = FALSE;
                else $blockMessage = TRUE;

                // Check rating status
                $ratingStatus = DB::table('user_ratings')->where('rate_user_id',$user_id)->where('rated_user_id',$checked_user_id)->count();
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array("Request_status"=>$requestMessage,'Crush_message'=>$crushMessage,'Block_status'=>$blockMessage,'Rating_status'=>$ratingStatus);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function list_favourite_group(Request $request){
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
            else {
                $user_id = $request->user_id;
                // $data = User_favorites::with(['groupDetail','groupImages'])->where('user_id',$user_id)->get()->toArray();
                $data = DB::table("user_favorites")
                        ->Join('groups', 'user_favorites.ids', '=', 'groups.id')
                        ->where("user_favorites.user_id",$user_id)
                        ->get(['heading','location','tagline','description','size','groups.id as group_id','groups.user_id as creater_user_id'])
                        ->toArray();
                foreach($data as $key => $value){
                    $images = DB::table('images')
                            ->where('group_id',$value->group_id)
                            ->pluck('image_path')
                            ->toArray();
                    if(!empty($images)){
                        foreach ($images as $value1) {
                            $value->group_images[] = url('uploads/group_images').'/'.$value->group_id.'/'.$value1;
                        }
                    }
                    else $value->group_images = [];
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function favourite_money_section(Request $request){
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
            else {
                $user_id = $request->user_id;
                $testData = DB::table('user_favorites')->where('user_id',$user_id)->where('type','!=','chat')->where('type','!=','group')->get(['ids','type','source'])->toArray();
                // print_r($testData); die;
                if(!empty($testData)){
                    $jobCategories = "(";
                    $sellCategories = "(";
                    $jobids = "(";
                    $sellids = "(";
                    $i = 0;
                    $j = 0;
                    foreach($testData as $key => $value){
                        if($value->source == "job" || $value->source == "Ads") $jcount[] = "0";
                        elseif($value->source == "sell") $scount[] = "0";
                    }
                    if(!empty($jcount)){
                        foreach($testData as $key => $value){
                            if($value->source == "job" || $value->source == "Ads"){
                                if(++$i == count($jcount)){
                                    $jobCategories .= "category_name like '%$value->type%')";
                                    $jobids .= "id like '%$value->ids%')";
                                }
                                else {
                                    $jobCategories .= "category_name like '%$value->type%' or ";
                                    $jobids .= "id like '%$value->ids%' or ";
                                }
                            }
                        }
                        $jobs = DB::select("Select *,user_id as creater_user_id from jobs where $jobCategories and $jobids");
                    } else $jobs = [];
                    if(!empty($scount)){
                        foreach($testData as $key => $value){
                            if($value->source == "sell"){
                                if(++$j == count($scount)){
                                    $sellCategories .= "category_name like '%$value->type%')";
                                    $sellids .= "id like '%$value->ids%')";
                                }
                                else {
                                    $sellCategories .= "category_name like '%$value->type%' or ";
                                    $sellids .= "id like '%$value->ids%' or ";
                                }
                            }
                        }
                        $sells = DB::select("Select *,user_id as creater_user_id from sell where $sellCategories and $sellids");
                    } else $sells = [];
                }
                else {
                    $jobs = [];
                    $sells = [];
                }
                if(!empty($jobs)){
                    foreach ($jobs as $key => $value) {
                        $value->logo = url('uploads/job_logos').'/'.$value->id.'/'.$value->logo;
                        $value->creater_user_name = DB::table('users')->where('id',$value->user_id)->pluck('name')->first();
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                        if (is_dir($user_images_root_path)){
                            $userImagePathArray = scandir($user_images_root_path);
                            if(!empty($userImagePathArray)){
                                $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$userImagePathArray[2];
                            }
                        }
                        else $value->creater_user_image = ' ';
                        $userId = $value->user_id;
                        $lastMessage = DB::table('chat')
                            ->Where(function ($query) use($user_id) {
                                $query->orwhere('sender_id', $user_id)
                                    ->orwhere('receiver_id',$user_id);
                            })
                            ->Where(function ($query) use($userId) {
                                $query->orwhere('receiver_id', $userId)
                                    ->orwhere('sender_id',$userId);
                            })
                            ->where('source',1)
                            ->orderby('id','desc')
                            ->pluck("message")
                            ->first();
                        if(empty($lastMessage)) $lastMessage = "";
                        else $lastMessage = json_decode($lastMessage);
                        $value->last_message = $lastMessage;
                    }
                }
                if(!empty($sells)){
                    foreach ($sells as $key => $value) {
                        $sell_images_root_path = public_path()."/uploads/sell_images".'/'.$value->id;
                        if (is_dir($sell_images_root_path)) $sellImagePathArray = scandir($sell_images_root_path);
                        else $sellImagePathArray = [];
                            if(!empty($sellImagePathArray)){
                                foreach ($sellImagePathArray as $key1 => $value1) {
                                    if ($key1 == 0 || $key1 == 1) continue;
                                    $value->sell_images[] = url('/uploads/sell_images').'/'.$value->id.'/'.$value1;
                                }
                            }
                            else $value->sell_images = [];
                            $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                            if (is_dir($user_images_root_path)){
                                $userImagePathArray = scandir($user_images_root_path);
                                if(!empty($userImagePathArray)){
                                    $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$userImagePathArray[2];
                                }
                            }
                            else $value->creater_user_image = ' ';
                            $value->creater_user_name = DB::table('users')->where('id',$value->user_id)->pluck('name')->first();
                            $userId = $value->user_id;
                            $lastMessage = DB::table('chat')
                                    ->Where(function ($query) use($user_id) {
                                        $query->orwhere('sender_id', $user_id)
                                            ->orwhere('receiver_id',$user_id);
                                    })
                                    ->Where(function ($query) use($userId) {
                                        $query->orwhere('receiver_id', $userId)
                                            ->orwhere('sender_id',$userId);
                                    })
                                    ->where('source',1)
                                    ->orderby('id','desc')
                                    ->pluck("message")
                                    ->first();
                            if(empty($lastMessage)) $lastMessage = "";
                            else $lastMessage = json_decode($lastMessage);
                            $value->last_message = $lastMessage;
                    }
                }
                $data = DB::table('chat')
                ->where('group_id', '0')
                ->where('source',1)
                // ->where('sender_id','!=',$user_id)
                ->orderBy('id','desc')
                ->Where(function ($query) use($user_id) {
                    $query->orwhere('sender_id', $user_id)
                          ->orwhere('receiver_id',$user_id);
                })
                ->groupBy('sender_id')
                ->groupBy('receiver_id')
                ->get(['sender_id','receiver_id','message as last_message','created_at'])
                ->toArray();
                // print_r($data);
                if(!empty($data)){
                    foreach ($data as $key => $value) {
                        if($value->sender_id == $user_id){
                            $test[] = $value->receiver_id;
                        }
                        else if($value->receiver_id == $user_id){
                            $test[] = $value->sender_id;
                        }
                    }
                }
                else $test = [];
                if(!empty($test)){
                    $uniqueTestArray = array_unique($test);
                    foreach ($uniqueTestArray as $key => $value) {
                        $responseArray[$key]['id'] = $value;
                        $responseArray[$key]['name'] = db::table('users')->where('id',$value)->pluck('name')->first();
                    
                       $lastMessage = DB::table('chat')
                                ->where('group_id', '0')
                                ->where('source',1)
                                ->orderBy('id','desc')
                                ->Where(function ($query) use($user_id) {
                                    $query->orwhere('sender_id', $user_id)
                                            ->orwhere('receiver_id',$user_id);
                                })
                                ->Where(function ($query) use($value) {
                                    $query->orwhere('sender_id', $value)
                                            ->orwhere('receiver_id',$value);
                                })
                                ->first(['message','created_at']);
                                // print_r($lastMessage);
                        if(!empty($lastMessage)){
                            $responseArray[$key]['last_message'] = json_decode($lastMessage->message);
                            $responseArray[$key]['created_at'] = $lastMessage->created_at;
                        }
                        else {
                            $responseArray[$key]['last_message'] = Null;
                            $responseArray[$key]['created_at'] = '0000-00-00';
                        }
                       $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value;
                        if (is_dir($user_images_root_path)){
                            $userImagePathArray = scandir($user_images_root_path);
                            if(!empty($userImagePathArray)){
                                $responseArray[$key]['user_image'] = url('/uploads/user_profile_images').'/'.$value.'/'.$userImagePathArray[2];
                            }
                        }
                        else $responseArray[$key]['user_image'] = ' ';
                    }
                } else $responseArray = [];
                $created_jobs = DB::select("Select *,user_id as creater_user_id from jobs where user_id = $user_id");
                if(!empty($created_jobs)){
                    foreach ($created_jobs as $key => $value) {
                        $value->logo = url('uploads/job_logos').'/'.$value->id.'/'.$value->logo;
                        $value->creater_user_name = DB::table('users')->where('id',$value->user_id)->pluck('name')->first();
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                        if (is_dir($user_images_root_path)){
                            $userImagePathArray = scandir($user_images_root_path);
                            if(!empty($userImagePathArray)){
                                $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$userImagePathArray[2];
                            }
                        }
                        else $value->creater_user_image = ' ';
                        $userId = $value->user_id;
                        $lastMessage = DB::table('chat')
                            ->Where(function ($query) use($user_id) {
                                $query->orwhere('sender_id', $user_id)
                                    ->orwhere('receiver_id',$user_id);
                            })
                            ->Where(function ($query) use($userId) {
                                $query->orwhere('receiver_id', $userId)
                                    ->orwhere('sender_id',$userId);
                            })
                            ->where('source',1)
                            ->orderby('id','desc')
                            ->pluck("message")
                            ->first();
                        if(empty($lastMessage)) $lastMessage = "";
                        else $lastMessage = json_decode($lastMessage);
                        $value->last_message = $lastMessage;
                    }
                }
                $created_sells = DB::select("Select *,user_id as creater_user_id from sell where user_id = $user_id");
                if(!empty($created_sells)){
                    foreach ($created_sells as $key => $value) {
                        $sell_images_root_path = public_path()."/uploads/sell_images".'/'.$value->id;
                        if (is_dir($sell_images_root_path)) $sellImagePathArray = scandir($sell_images_root_path);
                        else $sellImagePathArray = [];
                            if(!empty($sellImagePathArray)){
                                foreach ($sellImagePathArray as $key1 => $value1) {
                                    if ($key1 == 0 || $key1 == 1) continue;
                                    $value->sell_images[] = url('/uploads/sell_images').'/'.$value->id.'/'.$value1;
                                }
                            }
                            else $value->sell_images = [];
                            $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;
                            if (is_dir($user_images_root_path)){
                                $userImagePathArray = scandir($user_images_root_path);
                                if(!empty($userImagePathArray)){
                                    $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->user_id.'/'.$userImagePathArray[2];
                                }
                            }
                            else $value->creater_user_image = ' ';
                            $value->creater_user_name = DB::table('users')->where('id',$value->user_id)->pluck('name')->first();
                            $userId = $value->user_id;
                            $lastMessage = DB::table('chat')
                                    ->Where(function ($query) use($user_id) {
                                        $query->orwhere('sender_id', $user_id)
                                            ->orwhere('receiver_id',$user_id);
                                    })
                                    ->Where(function ($query) use($userId) {
                                        $query->orwhere('receiver_id', $userId)
                                            ->orwhere('sender_id',$userId);
                                    })
                                    ->where('source',1)
                                    ->orderby('id','desc')
                                    ->pluck("message")
                                    ->first();
                            if(empty($lastMessage)) $lastMessage = "";
                            else $lastMessage = json_decode($lastMessage);
                            $value->last_message = $lastMessage;
                    }
                }
                    $responseArrayMerged  = array_merge($jobs,$sells,$responseArray,$created_jobs,$created_sells);
                    $tempArr = array_unique(array_column($responseArrayMerged, 'id'));
                    $uniqueResponseArray = array_intersect_key($responseArrayMerged, $tempArr);
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_values($uniqueResponseArray);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function report_user(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'reported_user_id' => 'required|numeric'
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
                $block_userid = $request->user_id;
                $blocked_userid = $request->reported_user_id;
                $alreadyConnected = DB::table('connected_users')
                                    ->where('sender_user_id',$block_userid)
                                    ->where('receiver_user_id',$blocked_userid)
                                    ->first();
                if(empty($alreadyConnected)){
                    DB::table('connected_users')->insert(['sender_user_id'=>$block_userid,'receiver_user_id'=>$blocked_userid,'status'=>2]);
                }
                else{
                    DB::table('connected_users')->where('sender_user_id',$block_userid)->where('receiver_user_id',$blocked_userid)->update(['status'=>2]);
                }
                $response['status'] = "You have successfully report this user.";
                $response['error'] = "1";
                $response['data'] = "";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function list_created_chat_group(Request $request){
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
                $testData = DB::table('user_favorites')->where('user_id',$user_id)->where('type','chat')->pluck('ids')->toArray();
                if(!empty($testData)){
                    $userGroups = "(";
                    $count = count($testData);
                    foreach($testData as $key => $value){
                        if(++$key == $count)
                        $userGroups .= "id = '$value')";
                        else $userGroups .= "id = '$value' or ";
                    }
                    $data = DB::select("Select * from `groups` where $userGroups and type = 1");
                    foreach($data as $key => $value){
                        $images = DB::table('images')->orderBy('id','desc')->where('group_id', $value->id)->pluck('image_path')->first();
                            // print_r($images); die;
                            $value->group_images = url('/uploads/group_images').'/'.$value->id.'/'.$images;
                            $lastMessage = DB::table('chat')->where('group_id',$value->id)->orderby('id','desc')->pluck("message")->first();
                            if(empty($lastMessage)) $lastMessage = "";
                            else $lastMessage = json_decode($lastMessage);
                            $value->last_message = $lastMessage;
                    }
                }
                else $data = [];
                $created_chat_group = DB::select("Select * from `groups` where user_id = $user_id and type = 1");
                if(!empty($created_chat_group)){
                    foreach($created_chat_group as $key => $value){
                        $images = DB::table('images')->orderBy('id','desc')->where('group_id', $value->id)->pluck('image_path')->first();
                            // print_r($images); die;
                            $value->group_images = url('/uploads/group_images').'/'.$value->id.'/'.$images;
                            $lastMessage = DB::table('chat')->where('group_id',$value->id)->orderby('id','desc')->pluck("message")->first();
                            if(empty($lastMessage)) $lastMessage = "";
                            else $lastMessage = json_decode($lastMessage);
                            $value->last_message = $lastMessage;
                    }
                }
                else $created_chat_group = [];

                $responseArray  = array_merge($data,$created_chat_group);
                $tempArr = array_unique(array_column($responseArray, 'id'));
                $uniqueResponseArray = array_intersect_key($responseArray, $tempArr);
                // print_r(array_values($uniqueResponseArray)); die;

                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_values($uniqueResponseArray);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function add_user_prefrences_chat(Request $request){
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
                $distance = $request->distance;
                $data = DB::table('user_prefrences_chat')->where('user_id', $user_id)->first();
                if(empty($data)){
                    $true = DB::table('user_prefrences_chat')
                    ->insert(['user_id'=>$user_id,'distance'=>$distance]);
                    $response['status'] = "Prefrences added successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences added successfully. ";
                    return $response;
                }
                else {
                    DB::table('user_prefrences_chat')->where('user_id', $user_id)->update([
                        'distance' => $distance
                        ]);
                    $response['status'] = "Prefrences updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences updated successfully. ";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function view_all_prefrences(Request $request){
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
                $type = $request->type;
                if($type == "Group"){
                    $data = DB::table('user_prefrences_group')->where('user_id', $user_id)->first(['distance']);
                }
                else if($type == "Money"){
                    $data = DB::table('user_prefrences_group_money')->where('user_id', $user_id)->first(['distance','price','lower_price']);
                }
                else {
                    $data = DB::table('user_prefrences_chat')->where('user_id', $user_id)->first(['distance']);
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function add_user_prefrences_group(Request $request){
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
                $distance = $request->distance;
                $data = DB::table('user_prefrences_group')->where('user_id', $user_id)->first();
                if(empty($data)){
                    $true = DB::table('user_prefrences_group')
                    ->insert(['user_id'=>$user_id,'distance'=>$distance]);
                    $response['status'] = "Prefrences added successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences added successfully. ";
                    return $response;
                }
                else {
                    DB::table('user_prefrences_group')->where('user_id', $user_id)->update([
                        'distance' => $distance
                        ]);
                    $response['status'] = "Prefrences updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Prefrences updated successfully. ";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
}