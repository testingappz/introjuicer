<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Str;

use Illuminate\Contracts\Validation\ValidationException;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Cache\RateLimiter;
use App\Http\Controllers\Api\ApiController;
use Form;
use DB;
use Auth;
use Input;
use Redirect;
use Session;
use App\User;
use App\Model\Users;
use Site;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){

        $apiController = new ApiController();
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' =>'required'
        ]);

        if($validator->fails()){
            echo "PLease enter the missing fields";
        }else{
            $credentials = $this->credentials($request);
            if ($lockedOut = $this->hasTooManyLoginAttempts($request))
            {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }
            if ($this->guard()->attempt($credentials, false))
            {

                $user = Auth::user();
                $authToken = '';
                $token = Str::random(60);
                $authToken = hash('sha256', $token);

                $user->auth_token = hash('sha256', $token);
                $user->save();

                $data = ['user'=> $user];
                //Add device token starte here
                if(!empty($request->device_token))
                DB::table('device_token')->insert(['user_id'=>$user->id,'device_token'=>$request->device_token]);
                // end here
                // Add extra parameters into the login response start here
                $userData = Users::with(['userInterest','userLookingFor','userLanguage'])->where('id',$user->id)->get()->toArray();
                $interests = array();
    			$all_interests = isset($userData[0]['user_interest']) ? $userData[0]['user_interest'] : '';
    			if(!empty($all_interests)){
    				foreach($all_interests as $key => $interest){
	    				$interests[]= $interest['interest_type'];
	    			}
                }
                $comma_separated_interest = implode(",", $interests);
                $looking = array();

    			$all_lookings = isset($userData[0]['user_looking_for']) ? $userData[0]['user_looking_for'] : '';
    			if(!empty($all_lookings)){
    				foreach($all_lookings as $key => $userlooking){
	    				$looking[]= $userlooking['looking_for'];
	    			}
                }
                $languages = array();

    			$all_languages = isset($userData[0]['user_language']) ? $userData[0]['user_language'] : '';
    			if(!empty($all_languages)){
    				foreach($all_languages as $key => $language){
	    				$languages[]= $language['language'];
	    			}
    			}
                if(!empty($interests) and !empty($userData[0]['dob']) and !empty($looking) and !empty($userData[0]['relationship_status']) and !empty($languages))
				$profileStatus = "Complete";
				else $profileStatus = "Incomplete";

                //get user image from folder
                $user_images = url('/uploads/user_profile_images').'/'.$user->id;
                $path = public_path()."/uploads/user_profile_images".'/'.$user->id;
                if (is_dir($path)) $imagePathArray = scandir($path);
                else $imagePathArray = [];

                if (!empty($imagePathArray)) {
                    $data['user']['profile_Pic'] = $user_images.'/'.$imagePathArray[2];
                }
                else   $data['user']['profile_Pic'] = " ";


                // Add extra parameters into the login response end  here
                // Add user interests
                $data['user']['interest'] = $comma_separated_interest;
                $data['user']['status'] = $profileStatus;
                if($user->is_super_admin == 1)
                    return redirect('/home');
                else
                    return $apiController->success(null, $data);


            }else{
                if (!$lockedOut)
                $this->incrementLoginAttempts($request);
                // return $this->sendFailedLoginResponse($request);
                /*$rules['email'] = array('email' => 'unique:users,email');
                $validator = Validator::make($credentials, $rules);
                if ($validator->fails()) {
                    return $apiController->warning('Please enter valid password.', []);
                }
                // return $this->sendFailedLoginResponse($request);
                return $apiController->warning('Please enter valid email.', []); */
                return $apiController->warning('Please enter valid password.', []);
            }

        }
    }


    /***********
    logout API
    **********/
    public function logout(Request $request){
        $apiController = new ApiController();
        $validator = Validator::make($request->all(), [
            'user_id' => ['required','numeric'],
            'auth_token' => ['required'],
        ]);

        if($validator->fails()){
            return $apiController->error('general_validation', [
                'errors' => $validator->errors(),
            ]);
        }else{
            $check_userid = User::where('id',$request['user_id'])->first();

            if(!empty($request->device_token))
                DB::table('device_token')->where('user_id',$request['user_id'])->where('device_token',$request->device_token)->delete();

            if(!empty($check_userid)){

            //     $check_token = User::where(['id'=>$request['user_id'],'auth_token'=>$request['auth_token']])->get();

            //     if(count($check_token) > 0){

            //         User::where(['id'=>$request['user_id'],'auth_token'=>$request['auth_token']])
            //             ->update(['auth_token'=>null]);
            //             if(!empty($request->device_token))
            //             DB::table('device_token')->where('user_id',$request['user_id'])->where('device_token',$request->device_token)->delete();
                    $data = [
                        'id'           => $request['user_id'],
                    ];
                    return $apiController->success('Logout successfully',$data);

            //     }else{
            //         $data = [
            //             'auth_token'           => $request['auth_token'],
            //         ];
            //         return $apiController->warning('Auth token does not exists.',$data);
            //     }

            // }else{
            //     $data = [
            //         'id'           => $request['user_id'],
            //     ];
                // return $apiController->warning('User id does not exists',$data);
            }
        }

    }/****logout function ends here***/
}
