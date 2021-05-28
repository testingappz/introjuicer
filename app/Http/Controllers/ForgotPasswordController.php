<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Cache\RateLimiter;
use App\Http\Controllers\Api\ApiController;
use Form;
use Auth;
use Input;
use Redirect;
use Session;
use App\User;
// use App\Model\Users;
use Site;
use Mail;
use View;
use Hash;

class ForgotPasswordController extends Controller
{
    public function register(Request $request){
    	echo "I am in register";
    }

    /***************
	forgot password
	***************/
	public function forgotPassword(Request $request){

		if(!isset($request->email)){
			echo json_encode(array('success'=>0,'message'=>'Please provide an email.'));
			exit;
		}

		#check if email exists into the table
		try{
			$check_email = User::where('email',$request->email)->first();

		}catch(\Exception $e){
			return response()->json(['success' => '0','message'=>$e->getMessage()]);
		}


		#if email exists get the user password
		if(empty($check_email)){

			echo json_encode(array('success'=>0,'message'=>'Invalid Email id.'));
			exit;

		}else{
			#send email with otp to user to reset password

			$otpNumber = rand(100000, 999999);

			$update = User::where('email',$request->email)->update(['otp'=>$otpNumber]);

			$emails = $request->email;

			//$send_email_from = $_ENV['send_email_from'];
			$send_email_from = 'rupinder.k@iapptechnologies.com';
			$data = '';

			Mail::send('emails.send_otp', [
                    'data' => $otpNumber
            ], function ($message) use ($emails, $send_email_from) {

                $message->from($send_email_from, 'Introjuicer');

                $message->to($emails)->subject('Introjuicer OTP');
            });

            return response()->json([
                'success' => '1',
                'message' => 'OTP is sent via email',
                'otp'     => $otpNumber
            ]);
		}


	}/*****forgot password ends here****/

	/********************
	fn to reset password
	*********************/
	public function resetPassword(Request $request){

		if(!isset($request->email)){
			echo json_encode(array('success'=>0,'message'=>'Please provide an email.'));
			exit;
		}

		if(!isset($request->password)){
			echo json_encode(array('success'=>0,'message'=>'Please provide password.'));
			exit;
		}

		if(!isset($request->otp)){
			echo json_encode(array('success'=>0,'message'=>'Please provide otp.'));
			exit;
		}


		#check if email exists into the table
		try{
			$check_email = User::where('email',$request->email)->first();

		}catch(\Exception $e){
			return response()->json(['success' => '0','message'=>$e->getMessage()]);
		}


		#if email exists get the user password
		if(empty($check_email)){

			echo json_encode(array('success'=>0,'message'=>'Invalid Email id.'));
			exit;

		}else{

			if($check_email->otp == $request->otp){
				#change password for this email id
				$update = User::where('email',$request->email)->update(['password'=>Hash::make($request->password)]);

				if($update){
					echo json_encode(array('success'=>1,'message'=>'password Changed.'));
					exit;
				}else{
					echo json_encode(array('success'=>0,'message'=>'Please try again.'));
					exit;
				}
			}else{
				echo json_encode(array('success'=>0,'message'=>'Invalid OTP entered.'));
				exit;
			}
			

		}/***else ends here****/


	}/*****reset password ends here****/
}
