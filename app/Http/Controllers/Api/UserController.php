<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Cache\RateLimiter;
use App\Http\Controllers\Api\ApiController;
use Form;
//use Auth;
use Input;
use Redirect;
use Session;
use App\Model\Users;
use Site;
use Mail;
use View;
use Hash;
use DB;
class UserController extends Controller
{
    public function register(Request $request){
    	echo "I am in register";
    }
    /**************************
    fn to get user profile data
    **************************/
    public function getUserProfile(Request $request, $onlyData = false){


    	$apiController = new ApiController();
    	$user_id = isset($request->user_id) ? $request->user_id : 'null';

    	if($user_id == 'null'){
    		return $apiController->warning('Please enter user id', []);
    	}else if(!is_numeric($user_id)){
    		return $apiController->warning('Please enter numeric user id', []);
    	}else{
    		$user = Users::with(['userInterest','userLookingFor','userLanguage'])->where('id',$user_id)->get()->toArray();
    		//echo "<pre>";print_r($user);die;
    		$user_data = array();

    		if(!empty($user)){

    			$comma_separated_interest = '';
    			$interests = array();
    			$all_interests = isset($user[0]['user_interest']) ? $user[0]['user_interest'] : '';
    			if(!empty($all_interests)){
    				foreach($all_interests as $key=>$interest){
	    				$interests[]= $interest['interest_type'];
	    			}
    			}
    			$comma_separated_interest = implode(",", $interests);

    			$comma_separated_looking = '';
    			$looking = array();

    			$all_lookings = isset($user[0]['user_looking_for']) ? $user[0]['user_looking_for'] : '';
    			if(!empty($all_lookings)){
    				foreach($all_lookings as $key=>$userlooking){
	    				$looking[]= $userlooking['looking_for'];
	    			}
    			}
    			$comma_separated_looking = implode(",", $looking);

    			$comma_separated_language = '';
    			$languages = array();

    			$all_languages = isset($user[0]['user_language']) ? $user[0]['user_language'] : '';
    			if(!empty($all_languages)){
    				foreach($all_languages as $key=>$language){
	    				$languages[]= $language['language'];
	    			}
    			}
    			$comma_separated_language = implode(",", $languages);

				$user_images = url('/uploads/user_profile_images').'/'.$user_id;
				$path = public_path()."/uploads/user_profile_images".'/'.$user_id;
                if (is_dir($path)) $imagePathArray = scandir($path);
                else $imagePathArray = [];
                if (!empty($imagePathArray)) {
                    foreach($imagePathArray as $key => $value) {
                        if ($key == 0 || $key == 1) continue;
                        $image_url[] = $user_images.'/'.$value;
                    }
                }
                else $image_url = [];
				if(!empty($image_url) and !empty($interests) and !empty($user[0]['dob']) and !empty($looking) and !empty($user[0]['relationship_status']) and !empty($languages))
				$profileStatus = "Complete";
				else $profileStatus = "Incomplete";
				$siblings = isset($user[0]['siblings']) ? $user[0]['siblings'] : 'null';
				$siblings = "$siblings";
				$ratedBy = DB::table("user_ratings")->where('rated_user_id',$user_id)->count();
				$ratings = DB::table("users")->where("id",$user_id)->pluck('trust_rating')->first();
    			$user_data = [
					'name' => isset($user[0]['name']) ? $user[0]['name'] : 'null',
    				'first_name' => isset($user[0]['first_name']) ? $user[0]['first_name'] : 'null',
    				'last_name' => isset($user[0]['last_name']) ? $user[0]['last_name'] : 'null',
    				'religion' => isset($user[0]['religion']) ? $user[0]['religion'] : 'null',
    				'family' => isset($user[0]['family']) ? $user[0]['family'] : 'null',
    				'education' => isset($user[0]['education']) ? $user[0]['education'] : 'null',
    				'trust_rating' => isset($user[0]['trust_rating']) ? $user[0]['trust_rating'] : 'null',
    				'dob' => isset($user[0]['dob']) ? $user[0]['dob'] : 'null',
    				'profession' => isset($user[0]['profession']) ? $user[0]['profession'] : 'null',
    				'bio' => isset($user[0]['bio']) ? $user[0]['bio'] : 'null',
    				'gender' => isset($user[0]['gender']) ? $user[0]['gender'] : 'null',
    				'relationship_status' => isset($user[0]['relationship_status']) ? $user[0]['relationship_status'] : 'null',
    				'body_shape' => isset($user[0]['body_shape']) ? $user[0]['body_shape'] : 'null',
    				'height' => isset($user[0]['height']) ? $user[0]['height'] : 0,
    				'facebook_id' => isset($user[0]['facebook_id']) ? $user[0]['facebook_id'] : 'null',
    				'instagram_id' => isset($user[0]['instagram_id']) ? $user[0]['instagram_id'] : 'null',
    				'user_interest' => $comma_separated_interest,
    				'user_looking_for' => $comma_separated_looking,
					'user_language' => $comma_separated_language,
					'siblings' => $siblings,
					'user_images' =>$image_url,
					'status' => $profileStatus,
					'latitude' => isset($user[0]['latitude']) ? $user[0]['latitude'] : 'null',
					'longitude' => isset($user[0]['longitude']) ? $user[0]['longitude'] : 'null',
					'rating' => !empty($ratings) ? $ratings : "0",
					'rated_by' => $ratedBy,
					'age' => isset($user[0]['age']) ? $user[0]['age'] : 'null',
					'nationality' =>  isset($user[0]['nationality']) ? $user[0]['nationality'] : ' '
				];

				if($onlyData){
					return $user_data;
				}
				else{
					return $apiController->success(null, $user_data);
				}

    		}else{
    			return $apiController->warning('No data avialable', []);
    		}

    	}
    }/**********fn ends to get user profile data***********/
}
