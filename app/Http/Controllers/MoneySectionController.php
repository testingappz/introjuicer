<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Model\Jobs;
use App\Model\Sell;
use DB;
use App\Model\Users;

class MoneySectionController extends Controller
{
    public function get_sub_cats(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sub_cat_name' => 'required'
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
                $sub_cat_name = $request->sub_cat_name;
                $user_id = $request->user_id;
                $sub_cat_id = DB::table('sub_category')->where('sub_cat_name', $sub_cat_name)->pluck('id')->first();
                if(empty($sub_cat_id)){
                    $response['status'] = "No records matching your search. But stay tuned, we're growing fast!";
                    $response['error'] = "1";
                    $response['data'] = [];
                    return $response;
                }
                else {
                    // $cat_name = DB::table('sub_category_child')->where('sub_cat_id', $sub_cat_id)->pluck('sub_cat_child_name')->toArray();
                    $catName = DB::table('sub_category_child')->where('sub_cat_id', $sub_cat_id)->orderBy('sub_cat_child_name','asc')->get(['id','sub_cat_child_name'])->toArray();
                    // foreach ($catName as $key => $value) {
                    //     $count = DB::table('user_favorites')->where('ids',$value->id)->where('type',$value->sub_cat_child_name)->where('user_id',$user_id)->count();
                    //     $value->favorite = $count;
                    // }
                    foreach ($catName as $key => $value) {
                        $status = DB::table("user_interests")->where('user_id',$user_id)->where('interest_type','like',"$value->sub_cat_child_name")->count();
                        $value->status = $status;
                    }
                    $response['status'] = "Okay";
                    $response['error'] = "0";
                    $response['data'] = $catName;
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function create_job(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'company_name' => 'required',
                'logo' => 'required',
                'salary' => 'required',
                'location' => 'required',
                'description' => 'required'
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
                $job_id = $request->job_id;
                $user_id = $request->user_id;
                $job_heading = $request->job_heading;
                $company_name = $request->company_name;
                $logo = $request->logo;
                $salary = $request->salary;
                $location = $request->location;
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $description = $request->description;
                $category = $request->category;
                $email = $request->email;
                $phone = $request->phone;
                $website = $request->website;
                $parent_category_name = $request->parent_category_name;
                $payment_mode = $request->payment_mode;
                $file = $request->file('logo');
                foreach($file as $key => $value){
                    $imageName = $value->getClientOriginalName();
                }
                $jobs = DB::table('jobs')->where('id',$job_id)->first();
                // print_r($jobs); die;
                if(!empty($jobs)){
                    DB::table('jobs')
                            ->where('id',$job_id)
                            ->update(['job_heading'=>$job_heading,'company_name'=>$company_name,'logo'=>$imageName,'salary'=>$salary,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'description'=>$description,'email'=>$email,'phone'=>$phone,'website'=>$website,'category_name'=>$category,'payment_mode'=>$payment_mode]);
                    $jobImagePath = public_path('uploads/job_logos').'/'.$job_id;
                    //Remove images from the folder start here
                    if(is_dir($jobImagePath)){
                        foreach(glob($jobImagePath.'/*.*') as $job_image_root_path) {
                            unlink($job_image_root_path);
                        }
                    }
                    if($request->hasFile('logo')) {
                        $file = $request->file('logo');
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            $value->move($jobImagePath, $imageName);
                        }
                    }
                    $response['status'] = "Job updated successfully.";
                    $response['error'] = "0";
                    $response['data'] = "Job updated successfully.";
                }
                else{
                    DB::table('jobs')->insert(['user_id'=>$user_id,'job_heading'=>$job_heading,'company_name'=>$company_name,'logo'=>$imageName,'salary'=>$salary,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'description'=>$description,'email'=>$email,'phone'=>$phone,'website'=>$website,'category_name'=>$category,'parent_category_name'=>$parent_category_name,'payment_mode'=>$payment_mode]);
                    $id = DB::getPdo()->lastInsertId();
                    if($request->hasFile('logo')) {
                        $file = $request->file('logo');
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            $imagePath = public_path('uploads/job_logos').'/'.$id;
                            $value->move($imagePath, $imageName);
                        }
                    }
                    $response['status'] = "Job created successfully.";
                    $response['error'] = "0";
                    $response['data'] = "Job created successfully.";
                }
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function create_sell(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'heading' => 'required',
                'price' => 'required',
                'description' => 'required',
                'location' => 'required'
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
                $price = $request->price;
                // $price = $price / 100;
                $description = $request->description;
                $location = $request->location;
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $email = $request->email;
                $phone = $request->phone;
                $website = $request->website;
                $category = $request->category;
                $sell_id = $request->sell_id;
                $sell = DB::table('sell')->where('id',$sell_id)->first();
                if(!empty($sell)){
                    DB::table('sell')
                            ->where('id',$sell_id)
                            ->update(['user_id'=>$user_id,'heading'=>$heading,'price'=>$price,'description'=>$description,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'email'=>$email,'phone'=>$phone,'website'=>$website,'category_name'=>$category]);
                    $sellImagePath = public_path('uploads/sell_images').'/'.$sell_id;
                    //Remove images from the folder start here
                    if(is_dir($sellImagePath)){
                        foreach(glob($sellImagePath.'/*.*') as $sell_image_root_path) {
                            unlink($sell_image_root_path);
                        }
                    }
                    if($request->hasFile('sell_images')) {
                        $file = $request->file('sell_images');
                        // Store group images in folder according to user id
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            $value->move($sellImagePath, $imageName);
                        }
                    }
                    $response['status'] = "Sell updated successfully.";
                    $response['error'] = "0";
                    $response['data'] = "Sell updated successfully.";
                }
                else{
                    DB::table('sell')->insert(['user_id'=>$user_id,'heading'=>$heading,'price'=>$price,'description'=>$description,'location'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'email'=>$email,'phone'=>$phone,'website'=>$website,'category_name'=>$category]);
                    $id = DB::getPdo()->lastInsertId();
                    if($request->hasFile('sell_images')) {
                        $file = $request->file('sell_images');
                        // Store group images in folder according to user id
                        foreach($file as $key => $value){
                            $imageName = $value->getClientOriginalName();
                            $imagePath = public_path('uploads/sell_images').'/'.$id;
                            $value->move($imagePath, $imageName);
                        }
                    }
                    $response['status'] = "Sell created successfully.";
                    $response['error'] = "0";
                    $response['data'] = "Sell created successfully.";
                }
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function create_sub_cat_child(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'sub_cat_name' => 'required',
                'sub_cat_child_name' => 'required',
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
                $sub_cat_name = $request->sub_cat_name;
                $sub_cat_id = DB::table('sub_category')->where('sub_cat_name','like', $sub_cat_name)->pluck('id')->first();
                $sub_cat_child_name = $request->sub_cat_child_name;
                    $test = DB::table('sub_category_child')->where('sub_cat_id',$sub_cat_id)->where('sub_cat_child_name',$sub_cat_child_name)->first();
                    if(empty($test)){
                        DB::table('sub_category_child')->insert(['sub_cat_id'=>$sub_cat_id,'sub_cat_child_name'=>$sub_cat_child_name]);
                    }
                    else {
                        $response['status'] = "Sub category already exist, please use different name.";
                        $response['error'] = "0";
                        $response['data'] = "Sub category already exist, please use different name.";
                        return $response;
                    }
            }
            $response['status'] = "Sub category added successfully.";
            $response['error'] = "0";
            $response['data'] = "Sub category added successfully.";
            return $response;
        }
        catch(Exception $e){}
    }
    public function update_read_status(Request $request){
        $response = [];
        try{
            $message_id = $request->message_id;
            DB::table('chat')
                ->where('id',$message_id)
                ->update(['read_status'=>1]);
            $response['status'] = "Okay";
            $response['error'] = "0";
            $response['data'] = " Status updated. ";
            return $response;
        }
        catch(Exception $e){}
    }
    public function jobs_listing(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'category' => 'required'
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
                $category = $request->category;
                $user_id = $request->user_id;
                $userPrefData = DB::table('user_prefrences_group_money')->where('user_id', $user_id)->first();
                if(!empty($userPrefData)){
                    $usersData = DB::table('users')->where('id', $user_id)->first();
                    if(empty($request->latitude) and empty($request->longitude)){
                        $latitude = $usersData->latitude;
                        $longitude = $usersData->longitude;
                    }
                    else {
                        $latitude = $request->latitude;
                        $longitude = $request->longitude;
                    }
                    if(($userPrefData->distance == "98" || $userPrefData->distance == "99") and ($userPrefData->price < "999")) {
                        $data = DB::table('jobs')
                                ->where('category_name','like',$category)
                                ->whereRaw("salary < $userPrefData->price")
                                ->orderBy('id','desc')
                                ->get(['id','company_name','logo','location','description','salary','job_heading','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                    else if(($userPrefData->distance == "98" || $userPrefData->distance == "99") and ($userPrefData->price > "999")){
                        $data = DB::table('jobs')
                                ->where('category_name','like',$category)
                                ->whereRaw("salary > $userPrefData->price")
                                ->orderBy('id','desc')
                                ->get(['id','company_name','logo','location','description','salary','job_heading','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                    else {
                        $data = DB::table('jobs')
                                ->having('distance','<',$userPrefData->distance)
                                ->where('category_name','like',$category)
                                ->whereRaw("salary < $userPrefData->price")
                                ->orderBy('id','desc')
                                ->get(['id','company_name','logo','location','description','salary','job_heading','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                }
                else {
                    $usersData = DB::table('users')->where('id', $user_id)->first();
                    $latitude = $usersData->latitude;
                    $longitude = $usersData->longitude;

                    $data = DB::table('jobs')
                            ->where('category_name', $category)
                            ->orderBy('id','desc')
                            ->get(['id','company_name','logo','location','description','salary','job_heading','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                 * COS(RADIANS('$latitude'))
                                 * COS(RADIANS(Longitude - '$longitude'))
                                 + SIN(RADIANS(Latitude))
                                 * SIN(RADIANS('$latitude'))))) AS distance ")])
                            ->toArray();
                }
                foreach ($data as $key => $value) {
                    $value->logo = url('uploads/job_logos').'/'.$value->id.'/'.$value->logo;
                    $value->creater_user_name = DB::table('users')->where('id',$value->creater_user_id)->pluck('name')->first();
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->creater_user_id;
                    if (is_dir($user_images_root_path)){
                        $userImagePathArray = scandir($user_images_root_path);
                        if(!empty($userImagePathArray)){
                            $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->creater_user_id.'/'.$userImagePathArray[2];
                        }
                    }
                    else $value->creater_user_image = ' ';
                    $count = DB::table('user_favorites')->where('ids',$value->id)->where('type',$value->category_name)->where('user_id',$user_id)->count();
                    $value->favorite = $count;
                    // Get job images
                    // $jobLogosRootPath = public_path()."/uploads/job_logos".'/'.$value->id;
                    // if (is_dir($jobLogosRootPath)){
                    //     $jobImagePathArray = scandir($jobLogosRootPath);
                    //     if(!empty($jobImagePathArray)){
                    //         $value->logo = url('/uploads/user_profile_images').'/'.$value->creater_user_id.'/'.$userImagePathArray[2];
                    //     }
                    // }
                    // else  $value->logo = '';
                    $value->type = 'adv';
                }


                //  Get professional profile here
                    $res = DB::table('user_interests')
                    ->Join('professional_profile', 'user_interests.user_id', '=', 'professional_profile.user_id')
                    ->where('interest_type','LIKE',$category)
                    ->where('professional_profile.user_id', '!=' , $user_id)
                    ->get(['professional_profile.id','professional_profile.user_id','interest_type','name','education','email','website','phone','summary'])
                    ->toArray();

                    // Get total record end here
                    foreach ($res as $key => $value) {
                    $user_images_root_path = public_path()."/uploads/professional_profile_images".'/'.$value->user_id;
                    $user_images_display_path = url('/uploads/professional_profile_images').'/'.$value->user_id;
                    if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        foreach($imagePathArray as $key1 => $value1){
                            if ($key1 == 0 || $key1 == 1) continue;
                            $image_url = $user_images_display_path.'/'.$value1;
                            $value->images[] = $image_url;
                        }
                    }
                    else $value->images = [];

                    // Get roles

                    $roles = DB::table("professional_profile_roles")->where('professional_profile_id',$value->id)->get(['business_name','position','start_date','end_date']);
                    if(!empty($roles)) $value->roles = $roles;
                    else $value->roles = "";
                    $value->type = 'profile';

                    $count = DB::table('user_favorites')->where('ids',$value->id)->where('type',$category)->where('user_id',$user_id)->count();
                    $value->favorite = $count;

                    }


                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_merge($data,$res);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function job_listing_details(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'job_id' => 'required|numeric'
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
                $job_id = $request->job_id;
                $user_id = $request->user_id;
                $jobDetails = Jobs::with(['users'])->find($job_id)->toArray();
                $responseArray = [];
                $responseArray['company_name'] = $jobDetails['company_name'];
                $responseArray['job_heading'] = $jobDetails['job_heading'];
                $jobLogosRootPath = public_path()."/uploads/job_logos".'/'.$job_id;
                    if (is_dir($jobLogosRootPath)){
                        $jobImagePathArray = scandir($jobLogosRootPath);
                        if(!empty($jobImagePathArray)){
                            foreach($jobImagePathArray as $key => $value){
                                if ($key == 0 || $key == 1) continue;
                                $responseArray['logo'][] = url('/uploads/job_logos').'/'.$job_id.'/'.$value;
                            }
                        }
                    }
                else  $value->logo = '';
                // $responseArray['logo'] = url('uploads/job_logos').'/'.$job_id.'/'.$jobDetails['logo'];
                $responseArray['salary'] = $jobDetails['salary'];
                $responseArray['location'] = $jobDetails['location'];
                $responseArray['description'] = $jobDetails['description'];
                $responseArray['email'] = $jobDetails['email'];
                $responseArray['phone'] = $jobDetails['phone'];
                $responseArray['website'] = $jobDetails['website'];
                $responseArray['category_name'] = $jobDetails['category_name'];
                $responseArray['name'] = $jobDetails['users']['name'];
                $responseArray['user_id'] = $jobDetails['user_id'];
                $responseArray['latitude'] = $jobDetails['latitude'];
                $responseArray['longitude'] = $jobDetails['longitude'];
                $responseArray['parent_category_name'] = $jobDetails['parent_category_name'];
                $responseArray['payment_mode'] = $jobDetails['payment_mode'];
                $responseArray['favourite'] = DB::table('user_favorites')->where('ids',$job_id)->where('user_id',$user_id)->where('type',$jobDetails['category_name'])->count();
                // $data = DB::table('jobs')
                //     ->join('users' ,'users.id', '=','jobs.user_id')
                //     ->where('jobs.id',$job_id)
                //     ->first(['company_name','logo','salary','location','description','jobs.email','phone','website','category_name','users.name','users.id AS user_id']);
                //      $data->logo = url('uploads/job_logos').'/'.$job_id.'/'.$data->logo;
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$responseArray['user_id'];
                    if (is_dir($user_images_root_path)){
                        $userImagePathArray = scandir($user_images_root_path);
                        if(!empty($userImagePathArray)){
                            $responseArray['user_image'] = url('/uploads/user_profile_images').'/'.$responseArray['user_id'].'/'.$userImagePathArray[2];
                        }
                        else $responseArray['user_image'] = ' ';
                    }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $responseArray;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function sells_listing(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'category' => 'required'
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
                $category = $request->category;
                $user_id = $request->user_id;
                $userPrefData = DB::table('user_prefrences_group_money')->where('user_id', $user_id)->first();
                if(!empty($userPrefData)){
                    $usersData = DB::table('users')->where('id', $user_id)->first();
                    $latitude = $usersData->latitude;
                    $longitude = $usersData->longitude;
                    if(empty($request->latitude) and empty($request->longitude)){
                        $latitude = $usersData->latitude;
                        $longitude = $usersData->longitude;
                    }
                    else {
                        $latitude = $request->latitude;
                        $longitude = $request->longitude;
                    }
                    if(($userPrefData->distance == "98" || $userPrefData->distance == "99") and ($userPrefData->price < "999")){
                        $data = DB::table('sell')
                                ->where('category_name', $category)
                                ->whereRaw("price < $userPrefData->price")
                                ->get(['id','heading','price','location','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                    else if(($userPrefData->distance == "98" || $userPrefData->distance == "99") and ($userPrefData->price > "999")){
                        $data = DB::table('sell')
                                ->where('category_name', $category)
                                ->whereRaw("price > $userPrefData->price")
                                ->get(['id','heading','price','location','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                    else {
                        $data = DB::table('sell')
                                ->having('distance' , '<',$userPrefData->distance)
                                ->where('category_name', $category)
                                ->whereRaw("price < $userPrefData->price")
                                ->get(['id','heading','price','location','user_id as creater_user_id','category_name',\DB::raw("111.111 *
                                DEGREES(ACOS(LEAST(1.0, COS(RADIANS(Latitude))
                                     * COS(RADIANS('$latitude'))
                                     * COS(RADIANS(Longitude - '$longitude'))
                                     + SIN(RADIANS(Latitude))
                                     * SIN(RADIANS('$latitude'))))) AS distance ")])
                                ->toArray();
                    }
                }
                else {
                    $data = DB::table('sell')
                            ->where('category_name', $category)
                            ->get(['id','heading','price','location','user_id as creater_user_id','category_name'])
                            ->toArray();
                }
                foreach ($data as $key => $value) {
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
                        $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->creater_user_id;
                        if (is_dir($user_images_root_path)){
                            $userImagePathArray = scandir($user_images_root_path);
                            if(!empty($userImagePathArray)){
                                $value->creater_user_image = url('/uploads/user_profile_images').'/'.$value->creater_user_id.'/'.$userImagePathArray[2];
                            }
                        }
                        else $value->creater_user_image = ' ';
                        $value->creater_user_name = DB::table('users')->where('id',$value->creater_user_id)->pluck('name')->first();
                        $count = DB::table('user_favorites')->where('ids',$value->id)->where('type',$value->category_name)->where('user_id',$user_id)->count();
                        $value->favorite = $count;
                        $value->type = 'adv';
                }
                //  Get professional profile here
                $res = DB::table('user_interests')
                ->Join('professional_profile', 'user_interests.user_id', '=', 'professional_profile.user_id')
                ->where('interest_type','LIKE',$category)
                ->where('professional_profile.user_id', '!=' , $user_id)
                ->get(['professional_profile.id','professional_profile.user_id','interest_type','name','education','email','website','phone','summary'])
                ->toArray();

                // Get total record end here
                foreach ($res as $key => $value) {
                $user_images_root_path = public_path()."/uploads/professional_profile_images".'/'.$value->user_id;
                $user_images_display_path = url('/uploads/professional_profile_images').'/'.$value->user_id;
                if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
                else $imagePathArray = [];

                if (!empty($imagePathArray)) {
                    foreach($imagePathArray as $key1 => $value1){
                        if ($key1 == 0 || $key1 == 1) continue;
                        $image_url = $user_images_display_path.'/'.$value1;
                        $value->images[] = $image_url;
                    }
                }
                else $value->images = [];

                // Get roles

                $roles = DB::table("professional_profile_roles")->where('professional_profile_id',$value->id)->get(['business_name','position','start_date','end_date']);
                if(!empty($roles)) $value->roles = $roles;
                else $value->roles = "";
                $value->type = 'profile';

                $count = DB::table('user_favorites')->where('ids',$value->id)->where('type',$category)->where('user_id',$user_id)->count();
                    $value->favorite = $count;


                }

                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = array_merge($data,$res);
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function sell_listing_details(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sell_id' => 'required|numeric'
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
                $sell_id = $request->sell_id;
                $user_id = $request->user_id;
                $sellDetails = Sell::with(['users'])->find($sell_id)->toArray();
                $responseArray = [];
                $responseArray['heading'] = $sellDetails['heading'];
                $responseArray['price'] = $sellDetails['price'];
                $responseArray['description'] = $sellDetails['description'];
                $responseArray['location'] = $sellDetails['location'];
                $responseArray['email'] = $sellDetails['email'];
                $responseArray['phone'] = $sellDetails['phone'];
                $responseArray['website'] = $sellDetails['website'];
                $responseArray['category_name'] = $sellDetails['category_name'];
                $responseArray['name'] = $sellDetails['users']['name'];
                $responseArray['user_id'] = $sellDetails['user_id'];
                $responseArray['latitude'] = $sellDetails['latitude'];
                $responseArray['longitude'] = $sellDetails['longitude'];
                $responseArray['favourite'] = DB::table('user_favorites')->where('ids',$sell_id)->where('user_id',$user_id)->where('type',$sellDetails['category_name'])->count();
                // $data = DB::table('sell')
                //     ->join('users' ,'users.id', '=','sell.user_id')
                //     ->where('sell.id',$sell_id)
                //     ->first(['heading','price','description','location','sell.email','phone','website','category_name','users.id as user_id','name']);

                    $sell_images_root_path = public_path()."/uploads/sell_images".'/'.$sell_id;
                    if (is_dir($sell_images_root_path)){
                        $sellImagePathArray = scandir($sell_images_root_path);
                        if(!empty($sellImagePathArray)){
                            foreach ($sellImagePathArray as $key => $value) {
                                if ($key == 0 || $key == 1) continue;
                                $responseArray['sell_images'][] = url('/uploads/sell_images').'/'.$sell_id.'/'.$value;
                            }
                        }
                        else $responseArray['sell_images'] = [];
                    }
                    $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$responseArray['user_id'];
                    if (is_dir($user_images_root_path)){
                        $userImagePathArray = scandir($user_images_root_path);
                        if(!empty($userImagePathArray)){
                            $responseArray['user_image'] = url('/uploads/user_profile_images').'/'.$responseArray['user_id'].'/'.$userImagePathArray[2];
                        }
                        else $responseArray['user_image'] = ' ';
                    }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $responseArray;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function create_professional_profile(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric',
                'name' => 'required',
                'education' => 'required'
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
                $name = $request->name;
                $education = $request->education;
                $email = $request->email;
                $website = $request->website;
                $phone = $request->phone;
                $summary = $request->summary;
                $existingUser = DB::table('professional_profile')->where('user_id',$user_id)->first();
                if($request->hasFile('professional_profile_images')) {
                    $file = $request->file('professional_profile_images');
                    $imagePath = public_path('uploads/professional_profile_images').'/'.$user_id;
                        //Remove images from the folder start here
                        if(is_dir($imagePath)){
                            foreach(glob($imagePath.'/*.*') as $image_root_path) {
                                unlink($image_root_path);
                            }
                        }
                    // Store group images in folder according to user id
                    foreach($file as $key => $value){
                        $imageName = $value->getClientOriginalName();
                        $imagePath = public_path('uploads/professional_profile_images').'/'.$user_id;
                        $value->move($imagePath, $imageName);
                    }
                }
                if(empty($existingUser)){
                    DB::table('professional_profile')
                    ->insert(['user_id' => $user_id,'name'=>$name,'education'=>$education,'email'=>$email,'website'=>$website,'phone'=>$phone,'summary'=>$summary]);
                    // if($request->hasFile('professional_profile_images')) {
                    //     $file = $request->file('professional_profile_images');
                    //     // Store group images in folder according to user id
                    //     foreach($file as $key => $value){
                    //         $imageName = $value->getClientOriginalName();
                    //         $imagePath = public_path('uploads/professional_profile_images').'/'.$user_id;
                    //         $value->move($imagePath, $imageName);
                    //     }
                        $id = DB::getPdo()->lastInsertId();
                        $roles = $request->roles;
                        $data = trim($roles,'[]');
                        $data = explode('], [',$data);
                        foreach ($data as $key => $value) {
                            $insertdata = explode(',',$value);
                            $businessname = trim($insertdata[0],'"');
                            $position = trim($insertdata[1],'"');
                            $position = ltrim($position,' "');
                            $startDate = trim($insertdata[2],'"');
                            $startDate = ltrim($startDate,' "');
                            $endDate = trim($insertdata[3],'"');
                            $endDate = ltrim($endDate,' "');
                            DB::table('professional_profile_roles')
                                ->insert(['professional_profile_id' => $id,'business_name'=>$businessname,'position'=>$position,'start_date'=>$startDate,'end_date'=>$endDate]);
                        }
                // }
                    $response['status'] = "Professional profile created successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Professional profile created successfully. ";
                    return $response;
                }
                else {
                    DB::table('professional_profile_roles')->where('professional_profile_id',$existingUser->id)->delete();
                    $roles = $request->roles;
                    $data = trim($roles,'[]');
                    $data = explode('], [',$data);
                    foreach ($data as $key => $value) {
                        $insertdata = explode(',',$value);
                        $businessname = trim($insertdata[0],'"');
                        $position = trim($insertdata[1],'"');
                        $position = ltrim($position,' "');
                        $startDate = trim($insertdata[2],'"');
                        $startDate = ltrim($startDate,' "');
                        $endDate = trim($insertdata[3],'"');
                        $endDate = ltrim($endDate,' "');
                        DB::table('professional_profile_roles')
                            ->insert(['professional_profile_id' => $existingUser->id,'business_name'=>$businessname,'position'=>$position,'start_date'=>$startDate,'end_date'=>$endDate]);
                    }
                    DB::table('professional_profile')
                            ->where('user_id',$user_id)
                            ->update(['name'=>$name,'education'=>$education,'email'=>$email,'website'=>$website,'phone'=>$phone,'summary'=>$summary]);
                    $response['status'] = "Professional profile updated successfully. ";
                    $response['error'] = "0";
                    $response['data'] = "Professional profile updated successfully. ";
                    return $response;
                }
            }
        }
        catch(Exception $e){}
    }
    public function view_professional_profile(Request $request){
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
                $user = DB::table('professional_profile')->where('user_id',$user_id)->first();
                $professional_profile_images_display_path = url('/uploads/professional_profile_images').'/'.$user_id;
                    $path = public_path()."/uploads/professional_profile_images".'/'.$user_id;
                    if (is_dir($path)) $imagePathArray = scandir($path);
                    else $imagePathArray = [];

                    if (!empty($imagePathArray)) {
                        foreach($imagePathArray as $key => $value){
                            if ($key == 0 || $key == 1) continue;
                            $image_url = $professional_profile_images_display_path.'/'.$value;
                            $user->image[] = $image_url;
                        }
                    }
                    if(empty($user)) $user = [];
                    else {
                        $roles = DB::table("professional_profile_roles")->where('professional_profile_id',$user->id)->get(['business_name','position','start_date','end_date']);
                        if(!empty($roles)) $user->roles = $roles;
                    }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $user;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function money_section_chat_list(Request $request){
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
                $data = DB::table('chat')
                ->where('group_id', '0')
                ->where('source',1)
                ->where('sender_id','!=',$user_id)
                ->orderBy('id','desc')
                ->Where(function ($query) use($user_id) {
                    $query->orwhere('sender_id', $user_id)
                          ->orwhere('receiver_id',$user_id);
                })
                ->groupBy('sender_id')
                ->get(['sender_id as id','message as last_message','created_at'])
                ->toArray();
                foreach ($data as $key => $value) {
                   $value->name = db::table('users')->where('id',$value->id)->pluck('name')->first();
                   $value->last_message = json_decode($value->last_message);
                   $user_images_root_path = public_path()."/uploads/user_profile_images".'/'.$value->id;
                    if (is_dir($user_images_root_path)){
                        $userImagePathArray = scandir($user_images_root_path);
                        if(!empty($userImagePathArray)){
                            $value->user_image = url('/uploads/user_profile_images').'/'.$value->id.'/'.$userImagePathArray[2];
                        }
                    }
                    else $value->user_image = ' ';
                }
                $response['status'] = "Okay";
                $response['error'] = "0";
                $response['data'] = $data;
                return $response;
            }
        }
        catch(Exception $e){}
    }
    public function money_section_chat_history(Request $request){
        $response = [];
        try{
            $validator = Validator::make($request->all(), [
                'sender_id' => 'required|numeric',
                'receiver_id' => 'required|numeric'
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
                    ->where('source',1)
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


    // public function list_profiles(Request $request){
    //     $response = [];
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|numeric',
    //             'category' => 'required'
    //         ]);
    //         if ($validator->fails()){
    //             $errors = collect($validator->errors());
    //             $error  = $errors->unique()->first();
    //             $error = implode(" [] ", $error);
    //             $response['status'] = $error;
    //             $response['error'] = "1";
    //             $response['data'] = " ";
    //             return $response;
    //         }
    //         else{
    //             $user_id = $request->user_id;
    //             $category = $request->category;

    //             $data = DB::table('user_interests')
    //                     ->Join('professional_profile', 'user_interests.user_id', '=', 'professional_profile.user_id')
    //                     ->where('interest_type','LIKE',$category)
    //                     ->where('professional_profile.user_id', '!=' , $user_id)
    //                     ->get(['professional_profile.id','professional_profile.user_id','interest_type','name','education','email','website','phone','summary'])
    //                     // ->get()
    //                     ->toArray();

    //                 // Get total record end here
    //                 foreach ($data as $key => $value) {
    //                     $user_images_root_path = public_path()."/uploads/professional_profile_images".'/'.$value->user_id;
    //                     $user_images_display_path = url('/uploads/professional_profile_images').'/'.$value->user_id;
    //                     if (is_dir($user_images_root_path)) $imagePathArray = scandir($user_images_root_path);
    //                     else $imagePathArray = [];

    //                     if (!empty($imagePathArray)) {
    //                         foreach($imagePathArray as $key1 => $value1){
    //                             if ($key1 == 0 || $key1 == 1) continue;
    //                             $image_url = $user_images_display_path.'/'.$value1;
    //                             $value->images[] = $image_url;
    //                         }
    //                     }
    //                     else $value->images = [];

    //                     // Get roles

    //                     $roles = DB::table("professional_profile_roles")->where('professional_profile_id',$value->id)->get(['business_name','position','start_date','end_date']);
    //                     if(!empty($roles)) $value->roles = $roles;
    //                     else $value->roles = "";
    //                 }
    //                 $response['status'] = "Okay";
    //                 $response['error'] = "0";
    //                 $response['data'] = $data;
    //                 return $response;
    //         }
    //     }
    //     catch(Exception $e){}
    // }
}
