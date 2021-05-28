<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ChatCOntroller2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use File;
use DB;

class QuestionsController extends Controller
{
    public function starter_questions(Request $request){
        $data = DB::table("conversation_starters")->inRandomOrder()->take(20)->pluck('questions');
        $response = [];
        $response['status'] = "Okay";
        $response['error'] = "0";
        $response['data'] = $data;
        return $response;
    }
    public function reconnect_questions(Request $request){
        $id = $request->id;
        if(!empty($id)){
            $data = DB::table("reconnect_questions")->where('id','=',$id)->pluck('questions');
        }
        else $data = DB::table("reconnect_questions")->inRandomOrder()->take(20)->pluck('questions');
        $response = [];
        $response['status'] = "Okay";
        $response['error'] = "0";
        $response['data'] = $data;
        return $response;
    }
    public function add_secret_crush(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sender_user_id' => 'required|numeric',
                'receiver_user_id' => 'required|numeric'
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

                $sender_user_id = $request->sender_user_id;
                $receiver_user_id = $request->receiver_user_id;
                // Send push notification to other user who added secret crush
                $returnData = new ChatCOntroller2();
                $alreadyAddedCrush = DB::table('secret_crush')->where('sender_user_id',$receiver_user_id)->where('receiver_user_id',$sender_user_id)->first();

                // Add new crush
                $data = DB::table('secret_crush')->where('sender_user_id',$sender_user_id)->where('receiver_user_id',$receiver_user_id)->first();


                if(!empty($alreadyAddedCrush)){
                    $data = [];
                    $data['title'] = "You have a match. ";
                    $data['desc'] = "You have a match. ";
                    $device_token = DB::table('device_token')
                            ->Where(function ($query) use($sender_user_id,$receiver_user_id) {
                                $query->orwhere('user_id', $sender_user_id)
                                    ->orwhere('user_id',$receiver_user_id);
                            })
                            ->orderBy('id','DESC')
                            ->pluck('device_token')
                            ->toArray();

                    if(!empty($device_token)){
                        foreach($device_token as $key => $value){
                            $returnData->iOS($data, $value, $sender_user_id,'crushMatch');
                        }
                    }
                    DB::table('secret_crush')->insert(['sender_user_id'=>$receiver_user_id,'receiver_user_id'=>$sender_user_id,'status'=>0]);
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = "Secret crush Matched succesfully.";
                }
                elseif(empty($data)){
                    DB::table('secret_crush')->insert(['sender_user_id'=>$sender_user_id,'receiver_user_id'=>$receiver_user_id,'status'=>0]);
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = "Secret crush added succesfully.";

                    // Push notify
                    $data = [];
                    $data['title'] = "Secret Crush ";
                    $data['desc'] = "Someone added you as a secret cursh";
                    $device_token = DB::table('device_token')->where('user_id',$receiver_user_id)->orderBy('id', 'DESC')->pluck('device_token')->first();
                    if(!empty($device_token))
                    $returnData->iOS($data, $device_token, $receiver_user_id,'secretCrushAdded');
                }
                else {
                    DB::table('secret_crush')->where('sender_user_id',$sender_user_id)->where('receiver_user_id',$receiver_user_id)->delete();
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = "Secret crush deleted succesfully.";
                }
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function view_secret_crush(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sender_user_id' => 'required|numeric',
                'receiver_user_id' => 'required|numeric'
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
                $sender_user_id = $request->sender_user_id;
                $receiver_user_id = $request->receiver_user_id;
                $data = DB::table('secret_crush')->where('sender_user_id',$sender_user_id)->where('receiver_user_id',$receiver_user_id)->first();
                if(!empty($data)){
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = "Secret crush already exist.";
                }
                else {
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = "Secret crush not added yet.";
                }
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function delete_image(Request $request){
        $response = [];
        try{

            $validator = Validator::make($request->all(), [
                'image_url' => 'required'
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

                $image_url = parse_url($request->image_url);
                // unlink("uploads/user_profile_images/10/2020-09-23_00:13:11_0.jpeg");

                $image_realpath = ltrim($image_url['path'],"/");


                unlink($image_realpath);

                // Check if diretory is empty than remove directory
                $dir_path =  substr(ltrim($image_realpath), 0, strrpos( $image_realpath, '/'));
                if (! glob($dir_path . "/*")) {
                   rmdir($dir_path);
                }

                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "Image Removed Successfully.";
                return $response;
            }
        }
        catch(Exception $e){}
    }

    public function remove_user(Request $request){
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
                DB::table('group_users')->where('user_id',$request->user_id)->where('group_id',$request->group_id)->delete();
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = "User Removed Successfully.";
                return $response;
            }
        }
        catch(Exception $e){}
    }
}
