<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Str;
use DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => isset($data['phone_number'])?$data['phone_number']:""
        ]);
    }

    public function register(Request $request){
       
        $apiController = new ApiController;

        $validator = $this->validator($request->all());

        if ($validator->fails())
            return $apiController->error('general_validation', [
                'errors' => $validator->errors(),
            ]);
        
        $user = $this->create($request->all());

        $authToken = '';
        $token = Str::random(60);
        $authToken = hash('sha256', $token);

        $user = User::where('email',$request->email)->update(['auth_token'=>$authToken]);

        $user_data = User::where('email',$request->email)->first();
        $data = ['user'=> $user_data];        

        return $apiController->success(null, $data);
    }
    public function sign_up_process(Request $request){
        $user_id = $request->user_id;
        // Add device token
        if(!empty($request->device_token))
                DB::table('device_token')->insert(['user_id'=>$user_id,'device_token'=>$request->device_token]);
        // end here
        //Update age
        if(!empty($request->age)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['age' =>$request->age,'trust_rating'=>$ratings + 0.3]);
            // echo "hi"; die;
        } 
        //Update gender
        if(!empty($request->gender)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['gender' =>$request->gender,'trust_rating'=>$ratings + 0.3]);
        }
        //Update image
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
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['trust_rating'=>$ratings + 0.3]);
        }
       //Update user interests
       if(!empty($request->interest)){
            $interestArray = str_replace(array( '[', ']' ), '', $request->interest);
            $interestArray = explode(',',$interestArray);
            DB::table('user_interests')->where('user_id',$user_id)->delete();
            foreach($interestArray as $key => $value){
                $interest = trim($value, ' " ');
                DB::table('user_interests')
                ->insert(['interest_type' => $interest,'user_id'=>$user_id]);
            }
        $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
        DB::table('users')->where('id', $user_id)->update(['trust_rating'=>$ratings + 0.3]);
       }
       //Update relationship status 
       if(!empty($request->relationship)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['relationship_status' =>$request->relationship,'trust_rating'=>$ratings + 0.3]);
       }
       //Update looking for
       if(!empty($request->looking_for)){
            $lookingforArray = str_replace(array( '[', ']' ), '', $request->looking_for);
            $lookingforArray = explode(',',$lookingforArray);
            DB::table('user_looking_fors')->where('user_id',$user_id)->delete();
            foreach($lookingforArray as $key => $value){
                $looking_for = trim($value, ' " ');
                DB::table('user_looking_fors')
                ->insert(['looking_for' => $looking_for,'user_id'=>$user_id]);
            }
        $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
        DB::table('users')->where('id', $user_id)->update(['trust_rating'=>$ratings + 0.3]);
       }
       //Update short description
       if(!empty($request->short_desc)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['short_desc' => $request->short_desc,'trust_rating'=>$ratings + 0.3]);
       }
       //Update bio message
       if(!empty($request->message)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['bio' => $request->message,'trust_rating'=>$ratings + 0.3]);
       }
        //Update profession
        if(!empty($request->profession)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['profession' => $request->profession,'trust_rating'=>$ratings + 0.3]);
        }
        //Update education
        if(!empty($request->education)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['education' => $request->education,'trust_rating'=>$ratings + 0.3]);
        }
        //Update religion
        if(!empty($request->religion)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['religion' => $request->religion,'trust_rating' => $ratings + 0.3]);
        }
        // Update height
        if(!empty($request->height)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['height' => $request->height,'trust_rating'=>$ratings + 0.3]);
        }
        // Update siblings
        if(!empty($request->siblings)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['siblings' => $request->siblings,'trust_rating' => $ratings + 0.3]);
        }
        // Update nationality
        if(!empty($request->nationality)){
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['nationality' => $request->nationality,'trust_rating' => $ratings + 0.3]);
        }
        //Update spoken languages
        if(!empty($request->spoken_language)){
            $spoken_language = str_replace(array( '[', ']' ), '', $request->spoken_language);
            $spoken_languageArray = explode(',',$spoken_language);
            DB::table('int_user_languages')->where('user_id',$user_id)->delete();
            foreach($spoken_languageArray as $key => $value){
                $language = trim($value, ' " ');
                DB::table('int_user_languages')
                ->insert(['language' => $language,'user_id'=>$user_id]);
            }
            $ratings = DB::table('users')->where('id',$user_id)->pluck('trust_rating')->first();
            DB::table('users')->where('id', $user_id)->update(['trust_rating'=>$ratings + 0.3]);
        }
        $imagePath = public_path('uploads/user_profile_images').'/'.$user_id; 
        $imgUrl = scandir($imagePath);
        $imageViewUrl = url('uploads/user_profile_images').'/'.$user_id;
        $response['status'] = "Okay";
        $response['error'] = "0";
        $response['data'] = $imageViewUrl.'/'.$imgUrl[2];
        return $response;
    }
    public function delete_profile(Request $request){
        try{
            $user_id = $request->user_id;
            DB::table('connected_users')
            ->Where(function ($query) use($user_id) {
                $query->orwhere('sender_user_id', $user_id)
                ->orwhere('receiver_user_id',$user_id);
            })
            ->delete();
            $test = DB::table('groups')->where('user_id',$user_id)->pluck('id')->toArray();
            foreach($test as $value){
                DB::table('images')->where('group_id',$value)->delete();
                DB::table('tags')->where('group_id',$value)->delete();
            }
            DB::table('groups')->where('user_id',$user_id)->delete();
            DB::table('group_users')->where('user_id',$user_id)->delete();
            DB::table('hide_activities')->where('user_id',$user_id)->delete();
            DB::table('int_user_languages')->where('user_id',$user_id)->delete();
            DB::table('jobs')->where('user_id',$user_id)->delete();
            DB::table('professional_profile')->where('user_id',$user_id)->delete();
            $professional_profile_id = DB::table('professional_profile')->where('user_id',$user_id)->pluck('id');
            DB::table('professional_profile_roles')->where('professional_profile_id',$professional_profile_id)->delete();
            DB::table('secret_crush')
            ->Where(function ($query) use($user_id) {
                $query->orwhere('sender_user_id', $user_id)
                ->orwhere('receiver_user_id',$user_id);
            })
            ->delete();
            DB::table('sell')->where('user_id',$user_id)->delete();
            DB::table('user_feeds')->where('user_id',$user_id)->delete();
            DB::table('user_interests')->where('user_id',$user_id)->delete();
            DB::table('user_looking_fors')->where('user_id',$user_id)->delete();
            DB::table('user_prefrences')->where('user_id',$user_id)->delete();
            DB::table('user_prefrences_group_money')->where('user_id',$user_id)->delete();
            DB::table('user_ratings')
            ->Where(function ($query) use($user_id) {
                $query->orwhere('rate_user_id', $user_id)
                ->orwhere('rated_user_id',$user_id);
            })
            ->delete();
            DB::table('users')->where('id',$user_id)->delete();
            $response['status'] = "profile deleted successfully.";
            $response['error'] = "0";
            $response['data'] = "profile deleted successfully.";
            return $response;
        }
        catch(Exception $e){}
    }
}
