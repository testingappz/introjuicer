<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class UserRatingController extends Controller{

    public function add_rating(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'rate_user_id' => 'required|numeric',
                'rated_user_id' => 'required|numeric',
                'rating' => 'required',
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
                $rate_user_id = $request->rate_user_id;
                $rated_user_id = $request->rated_user_id;
                $rating = $request->rating;
                DB::table('user_ratings')->insert(['rate_user_id'=>$rate_user_id,'rated_user_id'=>$rated_user_id,'rating'=>$rating]);

                //Trust Worthy system start here
                //(how many months the scorer has been a member up to 10)
                $joinedMonths = DB::select("SELECT TIMESTAMPDIFF(month, created_at, CURDATE()) as month from users where id = $rate_user_id");
                $joinedMonths = $joinedMonths[0]->month;
                if($joinedMonths > 10 ) $joinedMonths = 10;
                $legitimacyScore1 = $joinedMonths / 10;
                // print_r($legitimacyScore1); die;
                // list chatting with users (count)
                $totalChattedUsers = DB::table('chat')
                ->where('group_id', '0')
                ->where('source',0)
                ->where('sender_id','!=',$rate_user_id)
                ->Where(function ($query) use($rate_user_id) {
                    $query->orwhere('sender_id', $rate_user_id)
                          ->orwhere('receiver_id',$rate_user_id);
                })
                ->distinct('sender_id')
                ->count();
                if($totalChattedUsers > 100) $totalChattedUsers = 100;
                $legitimacyScore2 = $totalChattedUsers / 100;
                // print_r($totalChattedUsers); die;
                // For (trustworthy multiplier between 0 and 1.5)
                $trustRating = DB::table('users')->where('id',$rate_user_id)->pluck('trust_rating')->first();
                if($trustRating < 200 and $trustRating > 101 ) $trustWorthyMultiplier = 1.5;
                else if($trustRating < 100 and $trustRating > 5) $trustWorthyMultiplier = 1;
                else if($trustRating < 5 and $trustRating > -5) $trustWorthyMultiplier = 0.5;
                else if($trustRating < -5 and $trustRating > -100) $trustWorthyMultiplier = 0.5;
                else $trustWorthyMultiplier = 0;
                $finalScore = $rating * $legitimacyScore1 * $legitimacyScore2 * $trustWorthyMultiplier;
                // print_r($finalScore); die;
                $trustRatingOfRatedUser = DB::table('users')->where('id',$rated_user_id)->pluck('trust_rating')->first();
                $finaltrustRating = $trustRatingOfRatedUser + $finalScore;
                DB::table('users')->where('id',$rated_user_id)->update(['trust_rating'=>$finaltrustRating]);

                //Reduce rating of the user who continuously given 10 negative ratings.
                
                $reduceRatings = DB::table("user_ratings")->where('rate_user_id',$rate_user_id)->orderBy('id')->take(10)->pluck('rating')->toArray();
                $x = 0;
                $i = 0;
                foreach ($reduceRatings as $key => $value) {
                    if($x > $value)  $i++;
                }
                if($i == 10){
                    $ratings = DB::table('users')->where('id',$rate_user_id)->pluck('trust_rating')->first();
                    DB::table('users')->where('id', $rate_user_id)->update(['trust_rating' => $ratings - 1]);
                    DB::table('user_ratings')->insert(['rate_user_id'=>$rate_user_id,'rated_user_id'=>$rated_user_id,'rating'=>0]);
                }
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = "Rating added successfully.";
            return $response;
            }
        }
        catch(Exception $e){}
    }
    public function view_rating(Request $request){
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
                $ratings = DB::table("user_ratings")->where("rated_user_id",$user_id)->avg("rating");
                $response['status'] ="";
                $response['error'] = "1";
                $response['data'] = $ratings;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function delete_group(Request $request){
        try{
            $group_id  = $request->group_id;
            DB::table('group_users')->where('group_id',$group_id)->delete();
            DB::table('chat')->where('group_id',$group_id)->delete();
            DB::table('images')->where('group_id',$group_id)->delete();
            DB::table('tags')->where('group_id',$group_id)->delete();
            DB::table('groups')->where('id',$group_id)->delete();
            $groupPath = public_path('uploads/group_images').'/'.$group_id; 
            if(is_dir($groupPath)){
                foreach(glob($groupPath.'/*.*') as $group_image_root_path) {
                    unlink($group_image_root_path);
                }
                rmdir($groupPath);
            }
            $response['status'] ="";
            $response['error'] = "0";
            $response['data'] = "You have successfully deleted group.";
            return $response;
        }
        catch(Exception $e){}
    }
    public function delete_jobs(Request $request){
        try{
            $job_id  = $request->job_id;
            DB::table('jobs')->where('id',$job_id)->delete();
            $jobImagePath = public_path('uploads/job_logos').'/'.$job_id; 
            if(is_dir($jobImagePath)){
                foreach(glob($jobImagePath.'/*.*') as $job_image_root_path) {
                    unlink($job_image_root_path);
                }
                rmdir($jobImagePath);
            }
            $response['status'] ="";
            $response['error'] = "0";
            $response['data'] = "You have successfully deleted job.";
            return $response;
        }
        catch(Exception $e){}
    }
    public function delete_sells(Request $request){
        try{
            $sell_id  = $request->sell_id;
            DB::table('sell')->where('id',$sell_id)->delete();
            $sellImagePath = public_path('uploads/sell_images').'/'.$sell_id; 
            if(is_dir($sellImagePath)){
                foreach(glob($sellImagePath.'/*.*') as $sell_image_root_path) {
                    unlink($sell_image_root_path);
                }
                rmdir($sellImagePath);
            }
            $response['status'] ="";
            $response['error'] = "0";
            $response['data'] = "You have successfully deleted sell.";
            return $response;
        }
        catch(Exception $e){}
    }
    public function join_default_group(Request $request){
        try{
            $user_id = $request->user_id;
            $group_id = $request->group_id;
            DB::table('group_users')->insert(['user_id'=>$user_id,'group_id'=>$group_id,'user_status'=>1,'user_type'=>2]);
            $response['status'] ="";
            $response['error'] = "0";
            $response['data'] = "You have successfully joined default group.";
            return $response;
        }
        catch(Exception $e){}
    }
}