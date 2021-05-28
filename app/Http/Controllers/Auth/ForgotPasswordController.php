<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Cache\RateLimiter;
use Form;
//use Auth;
use Input;
use Redirect;
use Session;
use App\User;
use Site;
//use Mail;
use Illuminate\Support\Facades\Mail;
use View;
use Hash;
use Illuminate\Support\Str;
use App\Mail\ForgotMail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;


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

            $token = Str::random(60);
            // $toEmail = $request->email;
            // $link = "https://introjuicer.iapplabz.co.in/reset_password/".$token;

            // $msg = "Please click on link to reset password : \n".$link;

            // $msg = wordwrap($msg,70);
            // $update = User::where('email',$request->email)->update(['mail_token'=>$token]);
            // // send email
            // mail($toEmail,"Forgot Password",$msg);
           
            //$data=$check_email->id; 
            $toEmail = $request->email;
            Mail::to($toEmail)->send(new ForgotMail($token));
            $update = User::where('email',$request->email)->update(['mail_token'=>$token]);
            return response()->json([
                'success' => '1',
                'message' => 'Please Check your email to reset password.'
            ]);
            #send email with otp to user to reset password

            // $otpNumber = rand(100000, 999999);

            // $update = User::where('email',$request->email)->update(['otp'=>$otpNumber]);

            // $emails = $request->email;

            // //$send_email_from = $_ENV['send_email_from'];
            // $send_email_from = 'rupinder.k@iapptechnologies.com';
            // $data = '';

            // Mail::send('emails.send_otp', [
            //         'data' => $otpNumber
            // ], function ($message) use ($emails, $send_email_from) {

            //     $message->from($send_email_from, 'Introjuicer');

            //     $message->to($emails)->subject('Introjuicer OTP');
            // });

            // return response()->json([
            //     'success' => '1',
            //     'message' => 'OTP is sent via email',
            //     'otp'     => $otpNumber
            // ]);
        }


    }/*****forgot password ends here****/

    public function reset_password($token)
    {

         //echo '<pre>'; print_r($token); die('here');
        $data = User::where('mail_token', $token)->first();
        if(empty($data)){
            echo "Page Expired.";die;
        }else{
            return view('reset_password')->with('data', $data);
        }
        

    }

    public function update_password()
    {

        $apiController = new ApiController;
        // echo '<pre>'; print_r($_POST); die('here');
        if($_POST['password']==$_POST['password_confirmation'])
        {
            $update_pass = User::where('id', $_POST['hidden_id'])->update(['password' => Hash::make($_POST['password_confirmation'])]);
            $data = ['email' => $_POST['hidden_email']];
            if($update_pass == 1){
                User::where('id', $_POST['hidden_id'])->update(['mail_token' => null]);
                
               // return $apiController->success('Password Updated Successfully',$data);  

               echo "Password Updated Successfully";die;
                
            }else{
                return $apiController->warning('Please try again.',$data);  
            } 
            
        }
        else
        {
            Session::flash('error_message', 'Password did not matched');
            return back()->withInput();
        }

    }

}
