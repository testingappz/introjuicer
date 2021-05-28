<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ChatCOntroller2;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;


class NotificationController extends Controller{


    public function reconnect_introduce(Request $request){
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
            else {
                $mytime = Carbon::now();
                $start_date = $mytime->toDateString();
                $user_id = $request->user_id;
                $reconnection = $request->reconnection;
                $introduction = $request->introduction;
                $only_view = $request->only_view;

                $res = DB::table('reconnect_introduce')
                    ->where('user_id',$user_id)
                    ->first(['reconnection','introduction']);

                if($only_view == true){
                    $response['status'] = "Okay.";
                    $response['error'] = "0";
                    $response['data'] = $res;
                }
                else{
                    if(empty($res)){
                        DB::table('reconnect_introduce')
                        ->insert(['user_id'=>$user_id,'reconnection'=>$reconnection,'start_date_reconnection'=>$start_date,'introduction'=>$introduction, 'start_date_introduction' => $start_date]);
                    }
                    else{
                        DB::table('reconnect_introduce')->where('user_id', $user_id)->update([
                            'reconnection' => $reconnection,
                            'start_date_reconnection' => $start_date,
                            'introduction' => $introduction,
                            'start_date_introduction' => $start_date,
                            ]);
                    }
                }
            }
            return $response;
        }
        catch(Exception $e){}
    }
    public function cron_for_notify_users(){
        $returnData = new ChatCOntroller2();
        $res = DB::table('reconnect_introduce')->whereNotNull('start_date_reconnection')->whereNotNull('start_date_introduction')->get()->toArray();

        foreach($res as $key => $value){


            $user_device_token = DB::table('device_token')->where('user_id',$value->user_id)->orderBy('id', 'ASC')->pluck('device_token')->first();
            //$device_tokens = DB::table('device_token')->where('user_id','!=',$value->user_id)->orderBy('id', 'ASC')->groupBy('user_id')->pluck('device_token')->toArray();
            $device_tokens = DB::table('device_token')->where('user_id','!=',$value->user_id)->orderBy('id', 'ASC')->pluck('device_token')->toArray();
            // Reconnection code goes here
            if(!empty($value->reconnection) && !empty($value->start_date_reconnection)) {


                $number_of_days = Carbon::now()->diffInDays($value->start_date_reconnection);
                if($number_of_days % $value->reconnection == 0){

                   // Get users for reconnection
                   $user_id = $value->user_id;
                   $user_data = DB::table('chat')
                        ->Where(function ($query) use($user_id) {
                            $query->orwhere('sender_id', $user_id)
                                  ->orwhere('receiver_id',$user_id);
                        })
                        ->where('message_type','=','text')
                        ->where('group_id',0)
                        ->where('source',0)
                        ->where( 'created_at', '>', Carbon::now()->subDays(7))
                        ->groupBy('sender_id')
                        ->get()
                        ->toArray();

                    if (!empty($user_data)) {
                        foreach ($user_data as $user_data_for_notification) {
                            if ($user_data_for_notification->sender_id != $user_id) {

                                // Random question id here
                                $rand = rand(0,99);
                                // device token for other user
                                $other_user_device_token = DB::table('device_token')->where('user_id',$user_data_for_notification->sender_id)->orderBy('id', 'ASC')->pluck('device_token')->first();

                                $data = [];
                                $data['title'] = "Reconnection.";
                                $data['desc'] = "Reconnection.";
                                $returnData->iOS($data, $user_device_token, $value->user_id,'reconnectionNotification','',(string)$rand);
                                $returnData->iOS($data, $other_user_device_token, $user_data_for_notification->sender_id,'reconnectionNotification','',(string)$rand);
                            }
                        }
                    }

                }
            }

            // Introduce code goes here
            if(!empty($value->introduction) && !empty($value->start_date_introduction)) {
                $number_of_days = Carbon::now()->diffInDays($value->start_date_introduction);
                if($number_of_days % $value->introduction == 0){
                    $data = [];
                    $data['title'] = "Introduction.";
                    $data['desc'] = "Introduction.";
                    if(!empty($device_tokens)){
                        foreach($device_tokens as $key => $value1){
                            $returnData->iOS($data, $value1, $value->user_id,'introductionNotification');
                        }
                    }
                }
            }
        }
    }
}
