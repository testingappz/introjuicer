<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Hash;
use DB;
use DateTime;
use App\Model\Users;
use App\Model\category;
use App\Model\Sub_category;
use App\Model\UserInterests;
use App\Model\Group;
use App\Model\Group_user;
use App\Model\image;
use App\Model\Jobs;
use App\Model\Sell;
use App\Model\Connected_users;
class ChatCOntroller2 extends Controller
{

    public function iOS($data, $devicetoken, $id,$type,$group_type = "",$question_id = ""){
		try{

				$tokens = $devicetoken;
		        $development 	= true; #make it false if it is not in development mode
		        $passphrase  	= ''; #your passphrase

		        // $payload 		= [];
                // $payload['aps'] = ['alert' => 'test', 'badge' => 1, 'sound' => 'default','mutable-content'=>1];

                $body['aps'] = array(
                    'alert' => array(
                        'title' => $data['title'],
                        'body' => $data['desc'],
                    ),
                    'sound' => 'default',
                    'id' => $id,
                    'type' => $type,
                    'group_type'=>$group_type,
                    'question_id' => $question_id,
                );
		        $payload 		= json_encode($body);

                // For development
                // $apns_cert 		= config_path() .DIRECTORY_SEPARATOR."IntrojuicerDevCerts.pem";
                //   For production
                //$apns_cert 		= config_path() .DIRECTORY_SEPARATOR."INTROJUICER-APNS-PROD.pem";

                // echo $apns_cert; die;
		        $apns_port 		= 2195;

		        if($development)
		        {
                    $apns_url 	= 'gateway.sandbox.push.apple.com';
                    $apns_cert 		= config_path() .DIRECTORY_SEPARATOR."apns_Certificates.pem";
		        }
		        else
		        {
                    $apns_url 	= 'gateway.push.apple.com';
                    $apns_cert 		= config_path() .DIRECTORY_SEPARATOR."apns_Certificates.pem";
                    // $apns_cert 		= config_path() .DIRECTORY_SEPARATOR."INTROJUICER-APNS-PROD.pem";
		        }

		        $stream_context = stream_context_create();
		        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
		        stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);

		        $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT,$stream_context);

		        $device_tokens = str_replace("<","",$tokens);
		        $device_tokens1= str_replace(">","",$device_tokens);
		        $device_tokens2= str_replace(' ', '', $device_tokens1);
				$device_tokens3= str_replace('-', '', $device_tokens2);

		        $apns_message  = chr(0) . pack('n', 32) . pack('H*', $device_tokens3) . chr(0) . chr(strlen($payload)) . $payload;
		        $msg = fwrite($apns, $apns_message);

				if(!$msg){
					 //file_put_contents('message.txt', $string.'Message not delivered');
					echo 'Message not delivered' . PHP_EOL;
					exit;
				}
		        @socket_close($apns);
		        @fclose($apns);



		}catch(Exception $e){}
    }
    // public function iOS($data, $devicetoken, $id,$type,$group_type = "")
    // {
    //     $deviceToken = $devicetoken;
    //     $development=true;
    //     $ctx = stream_context_create();
    //     $passphrase = "";
    //     if ($development) {
    //         $apns_url = 'gateway.sandbox.push.apple.com';
    //         $apns_cert = config_path() .DIRECTORY_SEPARATOR."apns_Certificates.pem";
    //     } else {
    //         $apns_url = 'gateway.push.apple.com';
    //         $apns_cert = config_path() .DIRECTORY_SEPARATOR."apns_Certificates.pem";
    //     }
    //     stream_context_set_option($ctx, 'ssl', 'local_cert', $apns_cert);
    //     stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    //     // echo $apns_cert;
    //     // echo $apns_url; die;
    //     // Open a connection to the APNS server
    //     $fp = stream_socket_client('ssl://'.$apns_url.':2195', $err,
    //         $errstr, 20, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
    //     if (!$fp)
    //         exit("Failed to connect: $err $errstr" . PHP_EOL);
    //     // Create the payload body
    //     $body['aps'] = array(
    //         'alert' => array(
    //             'title' => $data['title'],
    //             'body' => $data['desc'],
    //         ),
    //         'sound' => 'default',
    //         'id' => $id,
    //         'type' => $type,
    //         'group_type'=>$group_type,
    //     );
    //     // Encode the payload as JSON
    //     $payload = json_encode($body);
    //     // Build the binary notification
    //     $msg = chr(0) . pack('n', 32) . pack('H*',sprintf('%u', CRC32($deviceToken))) . pack('n', strlen($payload)) . $payload;
    //     // Send it to the server
    //     $result = fwrite($fp, $msg, strlen($msg));

    //     // Close the connection to the server
    //     fclose($fp);
    //     if (!$result)
    //         return false;
    //        // echo "not sent";
    //     else
    //         return true;
    //        // echo "sent succcessfully";
    // }

    public function test(Request $request){
        $data = [];
        $data['title'] = $request->title;
        $data['desc'] = $request->desc;
        $devicetoken = $request->devicetoken;
        $request_type = $request->request_type;
        $question_id = $request->question_id;
        $club_id = "12";
        $res = $this->iOS($data, $devicetoken, $club_id,$request_type,'',$question_id);

        print_r($res); die;
    }
    public function chat_categories(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'cat_name' => 'required',
            ]);
            if ($validator->fails()){
                return $validator->errors();
            }
            else{
                $cat_name = DB::table('categories')->where('cat_name', $request->cat_name)->first();
                if(empty($cat_name)){
                    $response['status'] = "No records matching your search. But stay tuned, we're growing fast!";
                    $response['error'] = "1";
                    $response['data'] = "";
                    return $response;
                }
                else {
                    $cat_id = $cat_name->id;
                    $user_id = $request->user_id;
                    $users = DB::table('categories')
                        ->Join('sub_category', 'categories.id', '=', 'sub_category.cat_id')
                        ->where('sub_category.cat_id',$cat_id)
                        ->get(['sub_category.sub_cat_name','sub_category.icon','joinable','sub_category.id as category_id'])
                        ->toArray();
                    foreach($users as $key => $value){
                        $status = DB::table("user_interests")->where('user_id',$user_id)->where('interest_type',$value->sub_cat_name)->count();
                        if(!empty($value->icon)) $url = url('uploads/category_icons').'/'.$value->icon;
                        else $url = " ";
                        $value->icon = $url;
                        $value->status = $status;
                    }
                }
            }
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = $users;
            return $response;
        }
        catch(Exception $e){}
    }
    public function chat_categories_with_popularity(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'cat_name' => 'required',
            ]);
            if ($validator->fails()){
                return $validator->errors();
            }
            else{
                $user_id = $request->user_id;
                if($request->cat_name == "connect"){
                    $users = DB::select("SELECT sub_category.sub_cat_name, COUNT(interest_type) AS count_interest, sub_category.icon,sub_category.id as category_id
                    FROM sub_category LEFT JOIN user_interests
                    ON sub_category.sub_cat_name = user_interests.interest_type
                    WHERE sub_category.cat_id = 1
                    GROUP BY sub_category.id
                    ORDER BY count_interest DESC");

                    foreach($users as $key => $value){
                        $value->icon = url('uploads/category_icons').'/'.$value->icon;
                        $status = DB::table("user_interests")->where('user_id',$user_id)->where('interest_type',$value->sub_cat_name)->count();
                        $value->status = $status;
                    }
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $users;
                    return $response;
                }
                else if($request->cat_name == "group"){
                    $users = DB::select("SELECT sub_category.sub_cat_name,sub_category.icon, COUNT(actual_tag_name) AS tag_count
                    FROM sub_category LEFT JOIN tags
                    ON sub_category.sub_cat_name LIKE CONCAT('%', SUBSTRING(tags.actual_tag_name,1,4), '%')
                    -- = tags.actual_tag_name
                    LEFT JOIN group_users on
                    group_users.group_id = tags.group_id
                    WHERE sub_category.cat_id = 2
                    GROUP BY sub_category.id
                    ORDER BY tag_count DESC");
                    foreach($users as $key => $value){
                        $value->icon = url('uploads/category_icons').'/'.$value->icon;
                        $status = DB::table("user_interests")->where('user_id',$user_id)->where('interest_type',$value->sub_cat_name)->count();
                        $value->status = $status;
                    }
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $users;
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function create_group(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'heading' => 'required',
                'tagline' => 'required',
                'description' => 'required',
                'size' => 'required|numeric',
                'tags' => 'required',
                'status' => 'required',
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
                $heading = $request->heading;
                $images = $request->images;
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $tagline = $request->tagline;
                $description = $request->description;
                $size = $request->size;
                $status = $request->status;
                $tags = $request->tags;
                $type = $request->type;
                $actual_tag_name = $request->actual_tag_name;
                $group_id = $request->group_id;
                $groupExistOrNot =  DB::table('groups')->where('id',$group_id)->first();
                if(!empty($groupExistOrNot)){
                    DB::table('groups')->where('id',$group_id)->update(
                        ['user_id'=>$user_id,'heading' => $heading, 'latitude' =>$latitude,'longitude'=>$longitude,'tagline'=>$tagline,'description'=>$description,'size'=>$size,'status'=>$status,'type'=>$type]
                    );
                    DB::table('images')->where('group_id',$group_id)->delete();
                    DB::table('tags')->where('group_id',$group_id)->delete();
                    if($request->hasFile('images')) {
                        $imagePath = public_path('uploads/group_images').'/'.$group_id;
                        //Remove images from the folder start here
                        if(is_dir($imagePath)){
                            foreach(glob($imagePath.'/*.*') as $image_root_path) {
                                unlink($image_root_path);
                            }
                        }
                        $file = $request->file('images');
                        // Store group images in folder according to user id
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            DB::table('images')->insert(['group_id'=>$group_id,'image_path'=>$imageName]);
                            $value->move($imagePath, $imageName);
                        }
                    }
                    $tagsArray = str_replace(array( '[', ']' ), '', $tags);
                    $tagsArray = explode(',',$tagsArray);
                    foreach($tagsArray as $key => $value){
                        $tags = trim($value,' " ');
                        DB::table('tags')->insert(['group_id'=>$group_id,'tag_name'=>$tags]);
                    }
                    $actualTagIds = DB::table('tags')->where('group_id',$group_id)->pluck('id')->first();
                    if(!empty($actualTagIds)){
                        $actualTagsArray = str_replace(array( '[', ']' ), '', $actual_tag_name);
                        $actualTagsArray = explode(',',$actualTagsArray);
                        // print_r($actualTagsArray); die;
                        foreach($actualTagsArray as $key => $value){
                            $actualTags = trim($value,' " ');
                            DB::table('tags')->where('id',$actualTagIds)->update(['actual_tag_name'=>$actualTags]);
                            $actualTagIds++;
                        }
                    }
                    if($type == 0)  $response['status'] = "Group updated successfully. ";
                    else  $response['status'] = "Chat group updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Group updated successfully. ";
                    return $response;
                }
                else{
                    DB::table('groups')->insert(
                        ['user_id'=>$user_id,'heading' => $heading, 'latitude' =>$latitude,'longitude'=>$longitude,'tagline'=>$tagline,'description'=>$description,'size'=>$size,'status'=>$status,'type'=>$type]
                    );
                    $id = DB::getPdo()->lastInsertId();
                    DB::table('group_users')->insert(['user_id'=>$user_id,'group_id'=>$id,'user_status'=>'1','user_type'=>'0']);
                    // Save images to folder
                    if($request->hasFile('images')) {
                        $file = $request->file('images');
                        // Store group images in folder according to user id
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            DB::table('images')->insert(['group_id'=>$id,'image_path'=>$imageName]);
                            $imagePath = public_path('uploads/group_images').'/'.$id;
                            $value->move($imagePath, $imageName);
                        }
                    }
                    // Save images to folder end here
                    //Tags Array Splitting
                    $tagsArray = str_replace(array( '[', ']' ), '', $tags);
                    $tagsArray = explode(',',$tagsArray);
                    foreach($tagsArray as $key => $value){
                        $tags = trim($value,' " ');
                        DB::table('tags')->insert(['group_id'=>$id,'tag_name'=>$tags]);
                    }
                    $actualTagIds = DB::table('tags')->where('group_id',$id)->pluck('id')->first();
                    if(!empty($actualTagIds)){
                        $actualTagsArray = str_replace(array( '[', ']' ), '', $actual_tag_name);
                        $actualTagsArray = explode(',',$actualTagsArray);
                        foreach($actualTagsArray as $key => $value){
                            $tags = trim($value,' " ');
                            DB::table('tags')->where('id',$actualTagIds)->update(['actual_tag_name'=>$tags]);
                            $actualTagIds++;
                        }
                    }
                    if($type == 0) $response['status'] = "Group created successfully. ";
                    else $response['status'] = "Chat group created successfully. ";
                    $response['error'] = "0";
                    $response['data'] = $id;
                    return $response;
                }
            }
        }
        catch(Exception $e){
            return $e->get_message();
        }
    }
    public function list_chat_group(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'tag_name' => 'required',
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
                $tag_name = $request->tag_name;
                $user_id = $request->user_id;
                $category_id = $request->category_id;
                $data = DB::table('user_prefrences_group')->where('user_id', $user_id)->first();
                if(!empty($data)){
                    $usersData = DB::table('users')->where('id', $user_id)->first(['latitude','longitude']);
                    if(empty($request->latitude) and empty($request->longitude)){
                        $latitude = $usersData->latitude;
                        $longitude = $usersData->longitude;
                    }
                    else {
                        $latitude = $request->latitude;
                        $longitude = $request->longitude;
                    }
                    if($data->distance == "99" || $data->distance == "98"){
                        $list_chat_groups = DB::select("SELECT groups.id,
                        groups.heading,
                        groups.tagline,
                        groups.location,
                        groups.size,
                        groups.user_id,
                        111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                * COS(RADIANS('$latitude'))
                                * COS(RADIANS(Longitude - '$longitude'))
                                + SIN(RADIANS(Latitude))
                                * SIN(RADIANS('$latitude'))))) AS distance
                        FROM   `tags`
                        INNER JOIN `groups`
                                ON groups.id = tags.group_id
                        INNER JOIN group_users
                                ON group_users.group_id = groups.id
                        WHERE  tags.tag_name LIKE '%$tag_name%' AND group_users.user_status = 1
                        AND groups.type = 0
                        AND ( groups.status LIKE '%public%'  OR (groups.status LIKE '%private%' AND  group_users.user_id = $user_id) )
                        GROUP  BY tags.group_id");
                    }
                    else {
                    $list_chat_groups = DB::select("SELECT groups.id,
                    groups.heading,
                    groups.tagline,
                    groups.location,
                    groups.size,
                    groups.user_id,
                    111.111 *
                        DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                             * COS(RADIANS('$latitude'))
                             * COS(RADIANS(Longitude - '$longitude'))
                             + SIN(RADIANS(Latitude))
                             * SIN(RADIANS('$latitude'))))) AS distance
                    FROM   `tags`
                    INNER JOIN `groups`
                            ON groups.id = tags.group_id
                    INNER JOIN group_users
                            ON group_users.group_id = groups.id
                    WHERE  tags.tag_name LIKE '%$tag_name%' AND group_users.user_status = 1
                    AND groups.type = 0
                    AND ( groups.status LIKE '%public%'  OR (groups.status LIKE '%private%' AND  group_users.user_id = $user_id) )
                    GROUP  BY tags.group_id Having distance < $data->distance");
                    }
                }
                else{
                    $usersData = DB::table('users')->where('id', $user_id)->first(['latitude','longitude']);
                    $latitude = $usersData->latitude;
                    $longitude = $usersData->longitude;

                    $list_chat_groups = DB::select("SELECT groups.id,
                    groups.heading,
                    groups.tagline,
                    groups.location,
                    groups.size,
                    groups.user_id,
                    111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                * COS(RADIANS('$latitude'))
                                * COS(RADIANS(Longitude - '$longitude'))
                                + SIN(RADIANS(Latitude))
                                * SIN(RADIANS('$latitude'))))) AS distance
                    FROM   `tags`
                    INNER JOIN `groups`
                            ON groups.id = tags.group_id
                    INNER JOIN group_users
                            ON group_users.group_id = groups.id
                    WHERE  tags.tag_name LIKE '%$tag_name%' AND group_users.user_status = 1
                    AND groups.type = 0
                    AND ( groups.status LIKE '%public%'  OR (groups.status LIKE '%private%' AND  group_users.user_id = $user_id) )
                    GROUP  BY tags.group_id");
                }
                if(empty($list_chat_groups)){
                    $list_chat_groups = [];
                }
                else {
                    foreach($list_chat_groups as $key => $value){
                        $total_members = DB::table('group_users')
                                    ->where('group_id', $value->id)
                                    ->where('user_status',1)
                                    ->count();
                        $value->total_members = $total_members;
                        $images = DB::table("images")
                                    ->where("group_id",$value->id)
                                    ->get('image_path')
                                    ->toArray();
                        if (!empty($images)) {
                            foreach ($images as $key1 => $value1) {
                                $group_images_display_path[] = url('/uploads/group_images').'/'.$value->id.'/'.$value1->image_path;
                                $value->images = $group_images_display_path;
                                unset($group_images_display_path);
                            }
                        }
                        else $value->images = [];
                        $favorite = DB::table('user_favorites')
                                    ->where('ids',$value->id)
                                    ->where('user_id',$user_id)
                                    ->where('type', 'like', 'group')
                                    ->count();
                        $value->favorite = $favorite;
                        $connectedToGroup = DB::table('group_users')->where('user_id',$user_id)->where('group_id',$value->id)->pluck('user_status')->first();
                        // 0 = not connected
                        // 1 = connected
                        // 2 = pending
                        // print_r($connectedToGroup); die;
                        if($connectedToGroup == '0') $connectedToGroupMessage = 2;
                        elseif($connectedToGroup == '1') $connectedToGroupMessage = 1;
                        else $connectedToGroupMessage = 0;
                        $value->connectedToGroup = $connectedToGroupMessage;
                        $value->type = 0;
                    }
                }
                // $defaultGroup = DB::table("groups")
                //                 ->select('id','location','user_id','heading','tagline','size', DB::raw("111.111 *
                //                     DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                //                         * COS(RADIANS('$latitude'))
                //                         * COS(RADIANS(Longitude - '$longitude'))
                //                         + SIN(RADIANS(Latitude))
                //                         * SIN(RADIANS('$latitude'))))) AS distance"))
                //                 ->where('type',2)
                //                 ->where('category_id',$category_id)
                //                 ->first();
                $defaultGroup = DB::table("groups")
                                ->where('type',2)
                                ->where('category_id',$category_id)
                                ->first();
                $resPonseDefaultGroup = [];
                $resPonseDefaultGroup[0]['id'] = $defaultGroup->id;
                $resPonseDefaultGroup[0]['location'] = $defaultGroup->location;
                $resPonseDefaultGroup[0]['user_id'] = $defaultGroup->user_id;
                $resPonseDefaultGroup[0]['heading'] = $defaultGroup->heading;
                $resPonseDefaultGroup[0]['tagline'] = $defaultGroup->tagline;
                $resPonseDefaultGroup[0]['size'] = $defaultGroup->size;
                $resPonseDefaultGroup[0]['type'] = 2;
                $resPonseDefaultGroup[0]['images'] = url('/uploads/default_group_image/default_group_image.png');
                $resPonseDefaultGroup[0]['total_members'] = DB::table('group_users')->where('group_id',$defaultGroup->id)->count();
                $connectedToDefaultGroup = DB::table('group_users')->where('user_id',$user_id)->where('group_id',$defaultGroup->id)->count();
                $resPonseDefaultGroup[0]['connectedToGroup'] = $connectedToDefaultGroup;
                // Check fav status
                $favorite = DB::table('user_favorites')
                    ->where('ids',$defaultGroup->id)
                    ->where('user_id',$user_id)
                    ->where('type', 'like', 'defaultGroup')
                    ->count();
                $resPonseDefaultGroup[0]['favorite'] = $favorite;
                // end here
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_merge($resPonseDefaultGroup,$list_chat_groups);
                return $response;

            }
        }
        catch(Exception $e){

        }
    }
    public function list_user_according_category(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'interest' => 'required',
                'looking_for' => 'required',
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
                $user_interest = $request->interest;
                $user_looking_for  = $request->looking_for;
                $test = DB::table('user_prefrences')->where('user_id', $user_id)->first();
                if(!empty($test)){
                    $usersData = DB::table('users')->where('id', $user_id)->first();
                    if(!empty($test->latitude) and !empty($test->longitude)){
                        $latitude = $test->latitude;
                        $longitude = $test->longitude;
                    }
                    else {
                        $latitude = $usersData->latitude;
                        $longitude = $usersData->longitude;
                    }
                    if(!empty($test->interests)){
                        $interestArray = explode(",",$test->interests);
                        $interests = "and (";
                        $count = count($interestArray);
                        foreach ($interestArray as $key => $value) {
                            if(++$key == $count)
                            $interests .= "i.interest_type like '%$value%' )";
                            else $interests .= "i.interest_type like '%$value%' or ";
                        }
                    }
                    else $interests = " ";
                    //Check religion in user prefrences
                    if(!empty($test->religion)){
                        $religionArray = explode(",",$test->religion);
                        $religion = "and (";
                        $count = count($religionArray);
                        foreach ($religionArray as $key => $value) {
                            if(++$key == $count)
                            $religion .= "religion like '%$value%' )";
                            else $religion .= "religion like '%$value%' or ";
                        }
                    }
                    else $religion = "";

                    //Check relationship status
                    if(!empty($test->relationship_status)){
                        $relationshipStatusArray = explode(",",$test->relationship_status);
                        $relationship_status = "and (";
                        $count = count($relationshipStatusArray);
                        foreach ($relationshipStatusArray as $key => $value) {
                            if(++$key == $count)
                            $relationship_status .= "relationship_status like '%$value%' )";
                            else $relationship_status .= "relationship_status like '%$value%' or ";
                        }
                    }
                    else $relationship_status = "";
                    //Check education in user prefrences
                    if(!empty($test->education))
                        // $education = "and education like '%$test->education%'";
                        $education = "";
                    else $education = "";

                    //Check nationality in user prefrences
                    if(!empty($test->nationality)){
                        // $nationality = "and nationality like '%$test->nationality%'";
                        $nationalityArray = explode(",",$test->nationality);
                        $nationality = "and (";
                        $count = count($nationalityArray);
                        foreach ($nationalityArray as $key => $value) {
                            if(++$key == $count)
                            $nationality .= "nationality like '%$value%' )";
                            else $nationality .= "nationality like '%$value%' or ";
                        }
                    }
                    else $nationality = "";
                    //  print_r($nationality);
                    //Check body shape in user prefrences
                    if(!empty($test->height)){
                        $pattern = "/[-\s,]/";
                        $testh = preg_split( $pattern, $test->height );
                        $maxHeight = max($testh);
                        $minheight = min($testh);
                        $height = " and (height between '$minheight' and '$maxHeight' )";
                    }
                    else $height = "";

                    //Check trust rating in user prefrences
                    if(!empty($test->trust_rating))
                        $trust_rating = "and trust_rating > '%$test->trust_rating%'";
                    else $trust_rating = "";

                    $age = $test->age;
                    $fromAge = substr($age,0,2);
                    $toAge = substr($age,3);

                    if($test->distance == "99" || $test->distance == "98"){

                        if($test->gender == "Either") {
                            $data = DB::select("SELECT u.id,u.name,age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude FROM user_interests as i inner join users as u on u.id = i.user_id where u.visibility like '%visible%' $religion $relationship_status $education $height $trust_rating $nationality $interests  GROUP BY u.id HAVING (age between '$fromAge' and '$toAge' ) ");
                        }
                        else {
                            $data = DB::select("SELECT u.id,u.name,age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude FROM user_interests as i inner join users as u on u.id = i.user_id where gender = '$test->gender' $religion $relationship_status $education $height $trust_rating $nationality $interests and u.visibility like '%visible%' GROUP BY u.id HAVING (age between '$fromAge' and '$toAge' ) ");
                        }
                    }
                    else{

                        if($test->gender == "Either") {
                            $data = DB::select("SELECT u.id,u.name,age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude,111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                    * COS(RADIANS('$latitude'))
                                    * COS(RADIANS(Longitude - '$longitude'))
                                    + SIN(RADIANS(Latitude))
                                    * SIN(RADIANS('$latitude'))))) AS distance FROM user_interests as i inner join users as u on u.id = i.user_id where u.visibility like '%visible%' $religion $relationship_status $education $height $trust_rating $nationality $interests  GROUP BY u.id HAVING distance < $test->distance and (age between '$fromAge' and '$toAge' ) ");
                        }
                        else {
                            $data = DB::select("SELECT u.id,u.name,age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude,111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                    * COS(RADIANS('$latitude'))
                                    * COS(RADIANS(Longitude - '$longitude'))
                                    + SIN(RADIANS(Latitude))
                                    * SIN(RADIANS('$latitude'))))) AS distance FROM user_interests as i inner join users as u on u.id = i.user_id where gender = '$test->gender'  $religion $relationship_status $education $height $trust_rating $nationality $interests and u.visibility like '%visible%'  GROUP BY u.id HAVING distance < $test->distance and (age between '$fromAge' and '$toAge' ) ");
                        }
                    }
                }
                else {
                    $data = DB::select("SELECT u.id,u.name,age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude FROM user_interests as i inner join users as u on u.id = i.user_id /*inner join user_looking_fors as r on u.id = i.user_id left join connected_users as se on se.sender_user_id  = u.id left join connected_users as re on re.receiver_user_id = u.id*/  where i.interest_type like '%$user_interest%' and u.visibility like '%visible%' /*AND u.id != $user_id
                    AND ( ( se.sender_user_id IS NULL
                            AND re.receiver_user_id IS NULL )
                           OR ( (se.sender_user_id IS NOT NULL AND se.status = 0)
                                 OR (re.receiver_user_id IS NOT NULL && re.status = 0   )
                              ) ) */ GROUP BY u.id");
                }
                //Following Query
                /* SELECT u.id,u.name, TIMESTAMPDIFF(YEAR, u.dob, CURDATE()) as age, u.profession, i.interest_type,u.bio,u.trust_rating,u.latitude,u.longitude,111.111 *
                   DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                        * COS(RADIANS('30.7046'))
                        * COS(RADIANS(Longitude - '76.7179'))
                        + SIN(RADIANS(Latitude))
                        * SIN(RADIANS('30.7046'))))) AS distance FROM user_interests as i inner join users as u on u.id = i.user_id inner join user_looking_fors as r on u.id = i.user_id where gender like '%male%' and trust_rating > '%2%'  and u.visibility like '%visible%' GROUP BY u.id HAVING distance < 99   and (age between '04' and '84' ) */
                // $data = DB::select("SELECT users.id,users.name,age,profession,interest.interest_type,bio,trust_rating,users.latitude,users.longitude FROM   `users`
                // LEFT JOIN user_interests AS interest
                //        ON interest.user_id = users.id
                // LEFT JOIN connected_users AS se
                //        ON se.sender_user_id = users.id
                // LEFT JOIN connected_users AS re
                //        ON re.receiver_user_id = users.id
                // WHERE  ( interest.interest_type LIKE '%$user_interest%' )
                //         AND users.id != $user_id
                //         AND ( ( se.sender_user_id IS NULL
                //                 AND re.receiver_user_id IS NULL )
                //             OR ( (se.sender_user_id IS NOT NULL AND se.status = 0)
                //                     OR (re.receiver_user_id IS NOT NULL && re.status = 0   )
                //                 ) )
                // GROUP  BY users.id ");
                foreach($data as $key => $value){
                    $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->id;
                    $path = public_path()."/uploads/user_profile_images".'/'.$value->id;
                    if (is_dir($path)) $imagePathArray = scandir($path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        foreach($imagePathArray as $key1 => $value1){
                            if ($key1 == 0 || $key1 == 1) continue;
                            $image_url = $user_images_display_path.'/'.$value1;
                            $value->image[] = $image_url;
                        }
                    }
                    else $value->image = [];
                    $ratedBy = DB::table("user_ratings")->where('rated_user_id',$value->id)->count();
                    $ratings = DB::table("users")->where("id",$value->id)->pluck('trust_rating')->first();
                    $value->rating = !empty($ratings) ? $ratings : "0";
                    $value->rated_by = $ratedBy;

                    // Connected or not
                    $userId = $value->id;
                    $connectedOrNot = DB::table('connected_users')
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('sender_user_id', $user_id)
                                  ->orwhere('receiver_user_id',$user_id);
                        })
                        ->Where(function ($query) use($userId) {
                            $query->orwhere('receiver_user_id', $userId)
                                  ->orwhere('sender_user_id',$userId);
                        })
                        ->pluck('status')
                        ->first();
                        // 0 = not connected
                        // 1 = connected
                        // 2 = pending
                        // print_r($connectedOrNot); die;
                        if($connectedOrNot == '0') $connectedOrNotMessage = 2;
                        elseif($connectedOrNot == '1') $connectedOrNotMessage = 1;
                        else $connectedOrNotMessage = 0;

                    $value->connected = $connectedOrNotMessage;
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function connect_users(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sender_user_id' => 'required|numeric',
                'receiver_user_id' => 'required|numeric',
            ]);
            if ($validator->fails()){
                $errors = collect($validator->errors());
                $error  = $errors->unique()->first();
                $error = implode(" [] ", $error);
                $response['status'] = $error;
                $response['error'] = "1";
                $response['data'] = "";
                return $response;
            }
            else {
                $sender_user_id = $request->sender_user_id;
                $receiver_user_id = $request->receiver_user_id;
                $test = DB::table('connected_users')
                        ->where('sender_user_id',$receiver_user_id)
                        ->where('receiver_user_id', $sender_user_id)
                        ->first();
                $requestAlreadyExists = DB::table('connected_users')
                        ->where('sender_user_id',$sender_user_id)
                        ->where('receiver_user_id', $receiver_user_id)
                        ->first();
                if(!empty($test)){
                    DB::table('connected_users')
                            ->where('sender_user_id',$receiver_user_id)
                            ->where('receiver_user_id', $sender_user_id)
                            ->update(['sender_user_id'=>$sender_user_id,'receiver_user_id'=>$receiver_user_id]);
                }
                else if(!empty($requestAlreadyExists)){
                    //Nothing happens here
                }
                else{
                    $connect_users = DB::table('connected_users')->insert(['sender_user_id'=>$sender_user_id,'receiver_user_id'=>$receiver_user_id,'status'=>'0']);
                }
                // Send push notification to other user who got friend request
                $sender_name = DB::table("users")->where('id',$sender_user_id)->pluck('name')->first();
                $data = [];
                $data['title'] = "You have a connection request.";
                $data['desc'] = "You have a connection request from ".$sender_name;
                $device_token = DB::table('device_token')->where('user_id',$receiver_user_id)->orderBy('id', 'DESC')->pluck('device_token')->first();
                if(!empty($device_token)){
                    $this->iOS($data, $device_token, $sender_user_id,'connectionRequestReceived');
                }
                // end here
                $response['status'] = "Friend request sent successfully.";
                $response['error'] = "0";
                $response['data'] = "Friend request sent successfully.";
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function connect_groups(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sender_user_id' => 'required|numeric',
                'receiver_group_id' => 'required|numeric',
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
                $sender_user_id = $request->sender_user_id;
                $receiver_group_id = $request->receiver_group_id;
                $groupSize = DB::table('groups')->where('id',$receiver_group_id)->pluck('size')->first();
                if($groupSize >= 1500){
                    $groupSize ++;
                    DB::table('groups')->where('id',$receiver_group_id)->update(['size'=>$groupSize]);
                }
                $memebersInGroup = DB::table('group_users')->where('group_id',$receiver_group_id)->count();
                if($memebersInGroup < $groupSize){
                    $connect_users = DB::table('group_users')->insert(['user_id'=>$sender_user_id,'group_id'=>$receiver_group_id,'user_status'=>'0','user_type'=>'2']);
                    if(true == $connect_users){
                        $response['status'] = "Group join request sent successfully.";
                        $response['error'] = "0";
                        $response['data'] = "Group join request sent successfully.";
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
    public function list_groups_details(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|numeric',
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
                if($type ==  0 ) $fav = "group";
                else if($type == 2) $fav = "defaultGroup";
                else $fav = "chat";
                $group = Group::with(['groupImages','groupTags'])
                        ->where('id',$request->group_id)
                        ->where('type',$type)
                        // ->select(\DB::raw("111.111 *
                        //     DEGREES(ACOS(LEAST(1.0, COS(RADIANS(groups.latitude))
                        //     * COS(RADIANS(''))
                        //     * COS(RADIANS(groups.longitude - ''))
                        //     + SIN(RADIANS(groups.latitude))
                        //     * SIN(RADIANS(''))))) AS distance "))
                        ->get()
                        ->toArray();

                // print_r($group);
                if($type == 2) {
                    $image_path[] = url('/uploads/default_group_image/default_group_image.png');
                }
                else {
                    $group_images = $group[0]['group_images'];
        			if(!empty($group_images)){
        				foreach($group_images as $key => $value){
    	    				$image_path[]= url('/uploads/group_images').'/'.$value['group_id'].'/'.$value['image_path'];
    	    			}
        			}
                    else {
                        $image_path = [];
                    }
                }
                
                $group_tags = $group[0]['group_tags'];
    			if(!empty($group_tags)){
    				foreach($group_tags as $key => $value){
	    				$tags[]= $value['tag_name'];
	    			}
                }
                else {
                    $tags = [];
                }

                // print_r($group);
                // Distance between two points
                $usersData = DB::table('users')->where('id', $user_id)->first(['latitude','longitude']);
                $latitude = $usersData->latitude;
                $longitude = $usersData->longitude;

                // Group lat long
                $grp_lat = $group[0]['latitude'];
                $grp_lng = $group[0]['longitude'];

                if(!empty($grp_lat) && !empty($grp_lng) && !empty($latitude) && !empty($longitude)){
                    $theta = $longitude - $grp_lng;
                    $dist = sin(deg2rad($latitude)) * sin(deg2rad($grp_lat)) +  cos(deg2rad($latitude)) * cos(deg2rad($grp_lat)) * cos(deg2rad($theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $distance =  ($miles * 1.609344);
                }
                else $distance = 0;


                $comma_separated_tags = implode(",", $tags);
                $responseGroup['id'] = $group[0]['id'];
                $responseGroup['heading'] = $group[0]['heading'];
                $responseGroup['location'] = $group[0]['location'];
                $responseGroup['tagline'] = $group[0]['tagline'];
                $responseGroup['description'] = $group[0]['description'];
                $responseGroup['size'] = $group[0]['size'];
                $responseGroup['status'] = $group[0]['status'];
                $responseGroup['image'] = $image_path;
                $responseGroup['tag'] = $comma_separated_tags;
                $responseGroup['creater_user_id'] = $group[0]['user_id'];
                $responseGroup['distance'] = $distance;
                $responseGroup['creater_user_name'] = DB::table('users')->where('id',$group[0]['user_id'])->pluck('name')->first();
                $responseGroup['favourite'] = DB::table('user_favorites')->where('ids',$group[0]['id'])->where('user_id',$user_id)->where('type','like',$fav)->count();
                //get user image from folder
                $user_images = url('/uploads/user_profile_images').'/'.$group[0]['user_id'];
                $path = public_path()."/uploads/user_profile_images".'/'.$group[0]['user_id'];
                if (is_dir($path)) $imagePathArray = scandir($path);
                else $imagePathArray = [];

                if (!empty($imagePathArray)) {
                    $responseGroup['creater_user_image'] = $user_images.'/'.$imagePathArray[2];
                }
                else   $responseGroup['creater_user_image'] = " ";
                // $test = Group_user::with(['groupUsers'])
                // ->where('user_status','1')
                //         ->where('group_id',$request->group_id)
                //         ->get()
                //         ->toArray();
                // print_r(json_encode($test));
                $groupMembers = Group_user::where('group_id',$request->group_id)
                    ->where('user_status','1')
                    ->get(['user_id'])
                    ->toArray();
                foreach($groupMembers as $key => $value){
                    $memberDetails[] = DB::table('users')->where('id', $value['user_id'])->first(['id','name']);
                }
                // print_r($memberDetails);
                if(!empty($memberDetails)){
                    foreach($memberDetails as $key => $value){
                        $user_images = url('/uploads/user_profile_images').'/'.$value->id;
                        $path = public_path()."/uploads/user_profile_images".'/'.$value->id;
                        if (is_dir($path)) $imagePathArray = scandir($path);
                        else $imagePathArray = [];

                        if (!empty($imagePathArray)) {
                        foreach($imagePathArray as $key1 => $value1) {
                                if ($key1 == 0 || $key1 == 1) continue;
                                $value->userimage = $user_images.'/'.$value1;
                            }
                        }
                        else  $value->userimage = " ";
                    }
                    $responseGroup['memberdetails'] = $memberDetails;
                }
                else {
                    $responseGroup['memberdetails'] = [];
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $responseGroup;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function list_user_groups(Request $request){
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
                $data = DB::table('group_users')
                        ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                        ->select('groups.id','groups.heading','groups.tagline','groups.size','group_users.user_id')
                        ->where('group_users.user_id',$request->user_id)
                        ->where('group_users.user_status','1')
                        ->get();
                foreach($data as $key => $value){
                    $images = DB::table('images')->where('group_id', $value->id)->get(['image_path']);
                    foreach ($images as $imagePath) {
                        // print_r($imagePath);
                        $value->group_images[] = url('/uploads/group_images').'/'.$value->id.'/'.$imagePath->image_path;
                    }
                    $memberDetails = DB::table('users')->where('id', $value->user_id)->select('name')->first();
                    $value->memberdetails = $memberDetails;

                    //Get User images from folder
                    $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->user_id;
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->user_id;

                    if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        foreach($imagePathArray as $key1 => $value1){
                            if ($key1 == 0 || $key1 == 1) continue;
                            $image_url = $user_images_display_path.'/'.$value1;
                            $memberDetails->user_images[] = $image_url;
                        }
                    }
                    else {
                        $memberDetails->user_images = " ";
                    }
                    // foreach(glob($user_images_root_path.'/*.*') as $image_root_path) {
                    //     $imagesPath[] = $image_root_path;
                    // }
                    // if(isset($imagesPath)){
                    //     foreach($imagesPath as $path){
                    //         $var = preg_split("#/#", $path);
                    //         //This check is for my local system public path location
                    //         if(!isset($var[10])){
                    //             $var[10] = $var[9];
                    //         }
                    //         $image_url = $user_images_display_path.'/'.$var[10];
                    //         $memberDetails->user_images[] = $image_url;
                    //     }
                    //    unset($imagesPath);
                    // }
                    // else{
                    //     $memberDetails->user_images = " ";
                    // }
                    //Get User images from folder Code end here
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function edit_user_profile(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'image' => 'required',
                'age' => 'required',
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
                $userExist = DB::table('users')->where('id', $user_id)->first();
                if(empty($userExist)){
                    $response['status'] = "No records matching your search. But stay tuned, we're growing fast!";
                    $response['error'] = "1";
                    $response['data'] = "No records matching your search. But stay tuned, we're growing fast!";
                    return $response;
                }
                else{
                    $message = $request->message;
                    $interest = $request->interest;
                    $looking_for = $request->looking_for;
                    $age = $request->age;
                    $gender = $request->gender;
                    $profession = $request->profession;
                    $realtionship = $request->realtionship;
                    $education = $request->education;
                    $religion = $request->religion;
                    $siblings = $request->siblings;
                    $height = $request->height;
                    $facebook = $request->facebook;
                    $instagram = $request->instagram;
                    $nationality = $request->nationality;
                    $spoken_language = $request->spoken_language;
                    // $bday = new DateTime($dob);
                    // $today = new Datetime(date('y.m.d'));
                    // $diff = $today->diff($bday);
                    DB::table('users')->where('id', $user_id)->update([
                        'age' =>$age,
                        'gender' => $gender,
                        'profession' => $profession,
                        'relationship_status' => $realtionship,
                        'education' => $education,
                        'religion' => $religion,
                        'height' => $height,
                        'facebook_id' => $facebook,
                        'instagram_id' => $instagram,
                        'bio' => $message,
                        'siblings' => $siblings,
                        'nationality' => $nationality
                        ]);
                        // Splitting Interest Array
                        $interestArray = str_replace(array( '[', ']' ), '', $interest);
                        $interestArray = explode(',',$interestArray);
                        DB::table('user_interests')->where('user_id',$user_id)->delete();
                        foreach($interestArray as $key => $value){
                            $interest = trim($value, ' " ');
                            DB::table('user_interests')
                            ->insert(['interest_type' => $interest,'user_id'=>$user_id]);
                        }
                        //Splitting user languages
                        $spoken_language = str_replace(array( '[', ']' ), '', $spoken_language);
                        $spoken_languageArray = explode(',',$spoken_language);
                        DB::table('int_user_languages')->where('user_id',$user_id)->delete();
                        foreach($spoken_languageArray as $key => $value){
                            $language = trim($value, ' " ');
                            DB::table('int_user_languages')
                            ->insert(['language' => $language,'user_id'=>$user_id]);
                        }
                        // Splitting Looking for Array
                        $lookingforArray = str_replace(array( '[', ']' ), '', $looking_for);
                        $lookingforArray = explode(',',$lookingforArray);
                        DB::table('user_looking_fors')->where('user_id',$user_id)->delete();
                        foreach($lookingforArray as $key => $value){
                            $looking_for = trim($value, ' " ');
                            DB::table('user_looking_fors')
                            ->insert(['looking_for' => $looking_for,'user_id'=>$user_id]);
                        }
                    if( $request->hasFile('image') ) {
                        $imagePath = public_path('uploads/user_profile_images').'/'.$user_id;
                        //Remove images from the folder start here
                        if(is_dir($imagePath)){
                            foreach(glob($imagePath.'/*.*') as $image_root_path) {
                                unlink($image_root_path);
                            }
                        }
                        //Remove images from the folder end here
                        $file = $request->file('image');
                        //Store images in folder according to user id
                        foreach($file as $key => $value){
                            $value->move($imagePath, $value->getClientOriginalName());
                        }
                    }
                    $returnData = new UserController();
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $returnData->getUserProfile($request,true);
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function one_to_one_chat(Request $request){
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
                $PendingRequests = DB::table('connected_users')
                            ->where('receiver_user_id', $user_id)
                            ->where('status', 0)
                            ->get(['sender_user_id'])
                            ->toArray();
                foreach ($PendingRequests as $key => $value) {
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->sender_user_id;
                    $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->sender_user_id;
                    if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                    }
                    else{
                        $value->user_image = "";
                    }
                    $value->user_name = DB::table('users')->where('id',$value->sender_user_id)->pluck('name')->first();
                }
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
                                $testRoseMessage = DB::table('chat')
                                    ->where('group_id',0)
                                    ->where('message_type','=','rose')
                                    ->where('read_status', 0)
                                    ->Where('receiver_id',$user_id)
                                    ->where('sender_id',$value->receive_user_id)
                                    ->first();
                                    // print_r($testRoseMessage);
                                if(empty($testRoseMessage)){
                                    $userNames[] = DB::table('users')
                                    ->where('id', $value->receive_user_id)
                                    ->select('id','name')
                                    ->first();
                                }
                            }
                            elseif($value->receive_user_id == $user_id){
                                $testRoseMessage = DB::table('chat')
                                    ->where('group_id', '0')
                                    ->where('message_type' , 'rose')
                                    ->where('read_status', 0)
                                    ->orderBy('id','desc')
                                    ->Where('receiver_id',$user_id)
                                    ->where('sender_id',$value->send_user_id)
                                    ->first();
                                    // print_r($testRoseMessage);
                                    if(empty($testRoseMessage)){
                                        $userNames[] = DB::table('users')
                                        ->where('id', $value->send_user_id)
                                        ->select('id','name')
                                        ->first();
                                    }
                            }
                        }
                if(!isset($userNames)){
                    $userNames = " ";
                }
                else{
                    foreach($userNames as $key => $value){
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->id;
                        $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->id;
                        if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                        else $imagePathArray = [];

                        if (!empty($imagePathArray)) {
                            $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                            $value->groups = "FALSE";
                        }
                        else{
                            $value->user_image = "";
                            $value->groups = "FALSE";
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
                        //         $value->groups = "FALSE";
                        //    unset($imagesPath);
                        // }
                        // else{
                        //     $value->user_image = "";
                        //     $value->groups = "FALSE";
                        // }
                        $userId = $value->id;
                        $lastMessageUser = DB::table('chat')
                        ->where('group_id', '0')
                        ->where('message_type','!=','rose')
                        ->where('source',0)
                        ->orderBy('id','desc')
                        ->Where(function ($query) use($userId) {
                            $query->orwhere('sender_id', $userId)
                                  ->orwhere('receiver_id',$userId);
                        })
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('sender_id', $user_id)
                                  ->orwhere('receiver_id',$user_id);
                        })
                        ->first(['message','created_at']);
                        if(!empty($lastMessageUser)){
                            $value->last_message = json_decode($lastMessageUser->message);
                            $value->created_at = $lastMessageUser->created_at;
                        }
                        else {
                            $value->last_message = null;
                            $value->created_at = "0000-00-00 00:00:00 ";
                        }
                    }
                }
                // print_r($userNames); die;
                if(is_array($userNames)){
                    $this->array_sort_by_column($userNames, 'created_at');
                }
                $roseMessage = DB::table('chat')
                        ->where('group_id', '0')
                        ->where('message_type' , 'rose')
                        ->where('read_status', 0)
                        ->orderBy('id','desc')
                        ->Where('receiver_id',$user_id)
                        ->get(['id as message_id','sender_id','receiver_id'])
                        ->toArray();
                foreach ($roseMessage as $key => $value) {
                    $sender_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->sender_id;
                    $receiver_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->receiver_id;
                    if (is_dir($sender_images_root_path)){
                        $senderImagePathArray = scandir($sender_images_root_path);
                        $value->sender_user_image = url('/uploads/user_profile_images').'/'.$value->sender_id.'/'.$senderImagePathArray['2'];
                    }
                    else $value->sender_user_image = "   ";

                    if(is_dir($receiver_images_root_path)){
                        $receiverImagePathArray = scandir($receiver_images_root_path);
                        $value->receiver_user_image = url('/uploads/user_profile_images').'/'.$value->receiver_id.'/'.$receiverImagePathArray['2'];
                    }
                    else $value->receiver_user_image = " ";
                    $senderUserName = DB::table('users')->where('id',$value->sender_id)->pluck('name')->first();
                    $value->message = $senderUserName ." just sent you a rose.";
                    $value->user_name = $senderUserName;
                }
                // $response['rose'] = $roseMessage;
                if(is_array($userNames)){
                    $mergedArray = array_merge($roseMessage,$userNames);
                }
                else $mergedArray =$roseMessage;
                $response['PendingRequests'] = $PendingRequests;
                if($userNames == " " && empty($roseMessage) && empty($PendingRequests)){
                    $response['status'] = "You are not connected to any user.";
                    $response['error'] = "1";
                    $response['data'] = " ";
                    return $response;
                }
                else {
                    $response['status'] = "okay";
                    $response['error'] = "0";
                    $response['data'] = $mergedArray;
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function group_listing_joined_users(Request $request){
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
                $userInGroups = DB::table('group_users')
                        ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                        ->where('group_users.user_id',$user_id)
                        ->where('group_users.user_status','1')
                        // ->where('groups.type',0)
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('type', 0)
                                ->orwhere('type',2);
                        })
                        ->get(['groups.id AS id', 'groups.heading AS name','groups.tagline','groups.size','groups.user_id as creater_user_id'])
                        ->toArray();
                $favorite_group_ids = DB::table('user_favorites')->where('type','group')->where('user_id',$user_id)->get('ids')->toArray();
                if(!empty($favorite_group_ids)){
                    foreach($favorite_group_ids as $key => $value){
                        $value->size = DB::table('groups')->where('id',$value->ids)->pluck('size')->first();
                        $value->group_type = DB::table('groups')->where('id',$value->ids)->pluck('type')->first();
                        $value->creater_user_id = DB::table('groups')->where('id',$value->ids)->pluck('user_id')->first();
                        $value->name = DB::table('groups')->where('id',$value->ids)->pluck('heading')->first();
                        $value->tagline = DB::table('groups')->where('id',$value->ids)->pluck('tagline')->first();
                        $group_images_display_path = url('/uploads/group_images').'/'.$value->ids;
                        $group_image = DB::table('images')->orderBy('id','desc')->where('group_id',$value->ids)->pluck('image_path')->first();
                        if (!empty($group_image)) {
                            $value->user_image = $group_images_display_path.'/'.$group_image;
                        }
                        else{
                            $value->user_image = "";
                        }
                        $groupId = $value->ids;
                        $last_message = DB::table('chat')
                        ->where('group_id', $groupId)
                        ->orderBy('created_at','desc')
                        ->first(['message','created_at']);
                        if(!empty($last_message)){
                            $value->last_message = json_decode($last_message->message);
                            $value->created_at = $last_message->created_at;
                        }
                        else {
                            $value->last_message = null;
                            $value->created_at = " ";
                        }
                        $value->id = $value->ids;
                    }
                    $value->type = "Favorite";
                }
                else $favorite_group_ids = [];
                foreach($userInGroups as $key => $value){
                    $group_images_display_path = url('/uploads/group_images').'/'.$value->id;
                    $group_image = DB::table('images')->orderBy('id','desc')->where('group_id',$value->id)->pluck('image_path')->first();
                    // print_r($group_image);
                    if (!empty($group_image)) {
                        $value->user_image = $group_images_display_path.'/'.$group_image;
                        $value->groups = "TRUE";
                        }
                    else{
                        $value->user_image = "";
                        $value->groups = "TRUE";
                    }
                    $groupId = $value->id;
                    $last_message = DB::table('chat')
                    ->where('group_id', $groupId)
                    ->orderBy('created_at','desc')
                    ->first(['message','created_at']);
                    if(!empty($last_message)){
                        $value->last_message = json_decode($last_message->message);
                        $value->created_at = $last_message->created_at;
                    }
                    else {
                        $value->last_message = null;
                        $value->created_at = " ";
                    }
                    $value->type = "created_or_joined";
                    $value->group_type = DB::table('groups')->where('id',$value->id)->pluck('type')->first();
                }

                // Pending Group connections
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
                            $value->group_type = DB::table('groups')->where('id',$value->group_id)->pluck('type')->first();
                        }
                    }
                    else $pendingGroupConnections = [];

                // View user send request to join groups
                $userRequestToGroup = DB::table('group_users')
                            ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                            ->where('groups.user_id',$user_id)
                            //->where('group_users.user_id',$user_id)
                            ->where('group_users.user_type',2)
                            ->where('group_users.user_status',0)
                            ->get(['groups.id as group_id','group_users.user_id'])
                            ->toArray();


                    if(!empty($userRequestToGroup)){
                        foreach ($userRequestToGroup as $key => $value) {
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
                                // sender name get here
                            $value->type = "User_to_group";
                            $value->heading = DB::table('users')->where("id",$value->user_id)->pluck('name')->first();
                        }
                    }
                    else $userRequestToGroup = [];

                $responseArray  = array_merge($userInGroups,$favorite_group_ids);
                $tempArr = array_unique(array_column($responseArray, 'id'));
                $uniqueResponseArray = array_intersect_key($responseArray, $tempArr);
                $response['status'] = "okay";
                $response['error'] = "0";
                $response['data'] = array_merge($pendingGroupConnections,$userRequestToGroup,array_values($uniqueResponseArray));
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function one_to_one_chat_history(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'sender_id' => 'required|numeric',
                'receiver_id' => 'required|numeric',
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
                $limit = $request->limit;
                $skip = ($limit - 1) * 50;
                $sender_id = $request->sender_id;
                $receiver_id = $request->receiver_id;
                $data = DB::table('chat')
                    ->where('group_id', '0')
                    ->where('source',0)
                    ->orderBy('id','desc')
                    ->Where(function ($query) use($sender_id) {
                        $query->orwhere('sender_id', $sender_id)
                            ->orwhere('receiver_id',$sender_id);
                    })
                    ->Where(function ($query) use($receiver_id) {
                        $query->orwhere('sender_id', $receiver_id)
                            ->orwhere('receiver_id',$receiver_id);
                    })
                    ->skip($skip)
                    ->take(50)
                    ->get(['chat.message','chat.sender_id','chat.receiver_id','chat.created_at']);
                    // Get total records
                    $totalPages = DB::table('chat')
                    ->where('group_id', '0')
                    ->Where(function ($query) use($sender_id) {
                        $query->orwhere('sender_id', $sender_id)
                            ->orwhere('receiver_id',$sender_id);
                    })
                    ->Where(function ($query) use($receiver_id) {
                        $query->orwhere('sender_id', $receiver_id)
                            ->orwhere('receiver_id',$receiver_id);
                    })
                    ->count();
                    $pages = ceil($totalPages / 50);
                    // Get total record end here
                    foreach ($data as $key => $value) {
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->sender_id;
                        $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->sender_id;
                        if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                        else $imagePathArray = [];

                        if (!empty($imagePathArray)) {
                            $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                        }
                        else{
                            $value->user_image = "";
                        }
                        $value->message = json_decode($value->message);
                    }
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $data;
                    $response['total_records'] = $pages;
                    return $response;
            }
        }
        catch(Exception $e){}
    }
    public function group_chat_history(Request $request){
        try{
            $validator = Validator::make($request->all(), [
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
                $limit = $request->limit;
                $skip = ($limit - 1) * 50;
                $groupChat = DB::table('chat')
                    ->orderBy('id','desc')
                    ->where('group_id', $request->group_id)
                    ->skip($skip)
                    ->take(50)
                    ->get(['chat.message','chat.sender_id','chat.created_at']);

                //Get user image from folder
                foreach($groupChat as $key => $value){
                    $value->message = json_decode($value->message);
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->sender_id;
                    $user_images_display_path = url('/uploads/user_profile_images').'/'.$value->sender_id;
                    if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        $value->user_image = $user_images_display_path.'/'.$imagePathArray[2];
                    }
                    else{
                        $value->user_image = "";
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
                    //     $value->user_image = "";
                    // }
                }
                // Get total records
                $totalPages = DB::table('chat')
                    ->where('group_id', $request->group_id)
                    ->count();
                $pages = ceil($totalPages / 50);
                // Get total record end here
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $groupChat;
                $response['total_records'] = $pages;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function group_listing(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'tag_name' => 'required'
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
                $tag_name = $request->tag_name;
                $data = DB::table('user_prefrences_chat')->where('user_id', $user_id)->first();
                if(!empty($data)){
                    $usersData = DB::table('users')->where('id', $user_id)->first();
                    if(empty($request->latitude) and empty($request->longitude)){
                        $latitude = $usersData->latitude;
                        $longitude = $usersData->longitude;
                    }
                    else {
                        $latitude = $request->latitude;
                        $longitude = $request->longitude;
                    }
                    if($data->distance == "99" || $data->distance == "98"){
                        $userInGroups = DB::table('group_users')
                        ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                        ->leftJoin('tags','tags.group_id', '=','groups.id')
                        ->where('group_users.user_id',$user_id)
                        ->where('tag_name','like',"%$tag_name%")
                        ->where('group_users.user_status','1')
                        ->where('groups.type',1)
                        ->groupBy('groups.id')
                        ->get(['groups.id AS id', 'groups.heading AS name','groups.tagline','groups.size', \DB::raw("111.111 *
                        DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                             * COS(RADIANS('$latitude'))
                             * COS(RADIANS(Longitude - '$longitude'))
                             + SIN(RADIANS(Latitude))
                             * SIN(RADIANS('$latitude'))))) AS distance ")])
                        ->toArray();
                    }
                    else {
                        $userInGroups = DB::table('group_users')
                            ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                            ->leftJoin('tags','tags.group_id', '=','groups.id')
                            ->having('distance','<',$data->distance)
                            ->where('group_users.user_id',$user_id)
                            ->where('tag_name','like',"%$tag_name%")
                            ->where('group_users.user_status','1')
                            ->where('groups.type',1)
                            ->groupBy('groups.id')
                            ->get(['groups.id AS id', 'groups.heading AS name','groups.tagline','groups.size', \DB::raw("111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                 * COS(RADIANS('$latitude'))
                                 * COS(RADIANS(Longitude - '$longitude'))
                                 + SIN(RADIANS(Latitude))
                                 * SIN(RADIANS('$latitude'))))) AS distance ")])
                            ->toArray();
                    }
                }
                else {
                    $userInGroups = DB::table('group_users')
                            ->Join('groups', 'groups.id', '=', 'group_users.group_id')
                            ->leftJoin('tags','tags.group_id', '=','groups.id')
                            ->where('group_users.user_id',$user_id)
                            ->where('tag_name','like',"%$tag_name%")
                            ->where('group_users.user_status','1')
                            ->where('groups.type',1)
                            ->groupBy('groups.id')
                            ->get(['groups.id AS id', 'groups.heading AS name','groups.tagline','groups.size'])
                            ->toArray();
                }
                foreach($userInGroups as $key => $value){
                    $group_images_display_path = url('/uploads/group_images').'/'.$value->id;
                    $group_image = DB::table('images')->orderBy('id','desc')->where('group_id',$value->id)->pluck('image_path')->first();

                    if (!empty($group_image)) {
                        $value->images = $group_images_display_path.'/'.$group_image;
                    }
                    else{
                        $value->images = "";
                    }
                //     foreach(glob($group_images_root_path.'/*.*') as $image_root_path) {
                //         $imagesPath = $image_root_path;
                //     }
                //     if(isset($imagesPath)){
                //         $var = preg_split("#/#", $imagesPath);
                //         //This check is for my local system public path location
                //         if(!isset($var[10])){
                //             $var[10] = $var[4];
                //         }
                //         $image_url = $group_images_display_path.'/'.$var[10];
                //         $value->user_image = $image_url;
                //     unset($imagesPath);
                // }
                // else{
                //     $value->user_image = "";
                // }
                    $groupId = $value->id;
                        $last_message = DB::table('chat')
                        ->where('group_id', $groupId)
                        ->orderBy('created_at','desc')
                        ->pluck('message')
                        ->first();
                        $value->last_message = json_decode($last_message);
                        $total_members = DB::table('group_users')
                                    ->where('group_id', $groupId)
                                    ->where('user_status',1)
                                    ->count();
                        $value->total_members = $total_members;
                        $favorite = DB::table('user_favorites')
                                    ->where('ids',$value->id)
                                    ->where('user_id',$user_id)
                                    ->where('type', 'like', 'chat')
                                    ->count();
                        $value->favorite = $favorite;
                        $value->type = 1;
                }


                $category_id = $request->category_id;

                // $defaultGroup = DB::table("groups")
                //                 ->select('id','location','user_id','heading','tagline','size', DB::raw("111.111 *
                //                     DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                //                         * COS(RADIANS('$latitude'))
                //                         * COS(RADIANS(Longitude - '$longitude'))
                //                         + SIN(RADIANS(Latitude))
                //                         * SIN(RADIANS('$latitude'))))) AS distance"))
                //                 ->where('type',2)
                //                 ->where('category_id',$category_id)
                //                 ->first();

                $defaultGroup = DB::table("groups")
                                ->where('type',2)
                                ->where('category_id',$category_id)
                                ->first();

                $resPonseDefaultGroup = [];
                $resPonseDefaultGroup[0]['id'] = $defaultGroup->id;
                $resPonseDefaultGroup[0]['user_id'] = $defaultGroup->user_id;
                $resPonseDefaultGroup[0]['location'] = $defaultGroup->location;
                $resPonseDefaultGroup[0]['heading'] = $defaultGroup->heading;
                $resPonseDefaultGroup[0]['tagline'] = $defaultGroup->tagline;
                $resPonseDefaultGroup[0]['size'] = $defaultGroup->size;
                $resPonseDefaultGroup[0]['type'] = 2;
                $resPonseDefaultGroup[0]['images'] = url('/uploads/default_group_image/default_group_image.png');
                $resPonseDefaultGroup[0]['total_members'] = DB::table('group_users')->where('group_id',$defaultGroup->id)->count();
                $connectedToDefaultGroup = DB::table('group_users')->where('user_id',$user_id)->where('group_id',$defaultGroup->id)->count();
                $resPonseDefaultGroup[0]['connectedToGroup'] = $connectedToDefaultGroup;
                // Check fav status
                $favorite = DB::table('user_favorites')
                    ->where('ids',$defaultGroup->id)
                    ->where('user_id',$user_id)
                    ->where('type', 'like', 'defaultGroup')
                    ->count();
                $resPonseDefaultGroup[0]['favorite'] = $favorite;

                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_merge($resPonseDefaultGroup,$userInGroups);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    function array_sort_by_column(&$array, $column, $direction = SORT_DESC ) {
        $reference_array = array();

        foreach($array as $key => $row) {
            $reference_array[$key] = $row->$column;
        }

        array_multisort($reference_array, $direction, $array);
    }
}
