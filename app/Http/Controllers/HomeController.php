<?php

namespace App\Http\Controllers;
use Auth;
use App\User;
use DB;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        if(Auth::user()->is_super_admin == 0){ Auth::logout();  return redirect('/login');}


        $users = User::count();
        $activeUsers = User::where('visibility', 'visible')->count();

            return view('home', ['data' => ['ttl_user' =>$users, 'active_user' => $activeUsers]]);
    }

    public function users(Request $request){
        if(Auth::user()->is_super_admin == 0){ Auth::logout();  return redirect('/login');}


        $search = $request->get('search') ? $request->get('search') : '';
        $page = $request->get('page') ? ($request->get('page')-1) : 0;
        $skip = $page*10;
        $limit = 15;

        $users = User::select('id', 'name', 'email', 'visibility')->where('is_super_admin', 0);
        if(!empty($search)){
            $search = '%'.$search.'%';
            $users = $users->where(function($query) use ($search){
                $query->where('name', 'like', $search)
                ->orWhere('email', 'like', $search);
            });

        }
        $users = $users->skip($skip)->limit($limit)->paginate();

        return view('users.index', ['data' => $users->appends(['search' => $request->get('search')])]);
    }

    public function updateUserStatus(Request $request){
        if(Auth::user()->is_super_admin == 0){ Auth::logout();  return redirect('/login');}
        $id = $request->get('id');
        $visibility = $request->get('visibility');
        $res = User::where('id', $id)->update(['visibility' => $visibility]);
        echo json_encode(['data' => $res]);
    }

    public function getUserInfo($id){
        $id = base64_decode($id);
        $user = User::select(['name','first_name as First Name','last_name as Last Name','gender','age','email','phone_number as Phone','facebook_id as Facebook','instagram_id as Instagram','profession','relationship_status as Relationship','religion','height','education','bio','nationality','short_desc as Description'])->find($id);
        return view('users.view', ['data' => $user]);
    }
    function logoutWeb(){
        Auth::logout();
        return redirect('/login');
    }
    function reconnect_qs(){
        $data = DB::table('reconnect_questions')->paginate(20);
        return view('reconnect',['data'=>$data]);
    }
    function starter_qs(){
        $data = DB::table('conversation_starters')->paginate(20);
        return view('conversation',['data'=>$data]);
    }
    function add_reconnect_qs(Request $request){
        return view('add_reconnect');
    }
    function add_starter_qs(Request $request){
        return view('add_conversation');
    }
    function adding_qs(Request $request){
        if(isset($request['reconnect_questions'])) {
            DB::table('reconnect_questions')->insert(['questions'=>$request->qs]);
            return redirect('/reconnect_qs');
        }
        else if(isset($request['add_category'])){
            DB::table('categories')->insert(['cat_name'=>$request->qs]);
            return redirect('/categories');
        }
        else if(isset($request['add_subcategory'])){


            if($request->hasFile('icon')) {
                $file = $request->file('icon');
                // Store group images in folder according to user id
 
                $imageName = preg_replace('/\s+/', '', $file->getClientOriginalName());
                $imagePath = public_path('uploads/category_icons');
                $file->move($imagePath, $imageName);
            }
            if(!isset($imageName)) $imageName = '';

            DB::table('sub_category')->insert(['cat_id'=>$request->category_id,'sub_cat_name'=>$request->qs,'icon' => $imageName]);
            return redirect('/categories');
        }
        else if(isset($request['update_category'])){
            DB::table('categories')->where('id',$request->category_id)->update(['cat_name'=>$request->qs]);
            return redirect('/categories');
        }
        else if(isset($request['update_subcategory'])){

            if($request->hasFile('icon')) {
                $file = $request->file('icon');
                // Store group images in folder according to user id

                $imageName = preg_replace('/\s+/', '', $file->getClientOriginalName());
                $imagePath = public_path('uploads/category_icons');
                $file->move($imagePath, $imageName);
            }
            if(!isset($imageName)) $imageName = '';

            DB::table('sub_category')->where('id',$request->subcategory_id)->update(['sub_cat_name'=>$request->qs,'icon' => $imageName]);
            return redirect('/categories');
        }
        else {
            DB::table('conversation_starters')->insert(['questions'=>$request->qs]);
            return redirect('/starter_qs');
        }
    }
    function categories(Request $request){
        $data = DB::table('categories')->get();
        return view('categories',['data'=>$data]);
    }

    function groups(Request $request,$group_id = 0){
        if($group_id != 0){
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
            return redirect('/groups');
        }
        else {
            $data = DB::table('groups')->where('type',0)->paginate(20);
            return view('groups',['data'=>$data]);
        }
    }
    function chat_groups(){
        $data = DB::table('groups')->where('type',1)->paginate(20);
        return view('groups',['data'=>$data]);
    }

    function sub_categories(Request $request){
        $id = base64_decode($request->id);
        $data = DB::table('sub_category')->where('cat_id',$id)->get();
        $link = $data[0]->cat_id;
        return view('sub_category',['data'=>$data,'link'=>$link]);
    }
    function add_category(Request $request){
        return view('add_category');
    }
    function add_subcategory(Request $request){
        $id = $request->id;
        return view('add_subcategory',['id'=>$id]);
    }
    function update_cat(Request $request){
        $id = $request->id;
        $name = DB::table('categories')->where('id',$id)->pluck('cat_name')->first();
        return view('update_cat',['id'=>$id,'name'=>$name]);
    }
    function update_subcat(Request $request){
        $id = base64_decode($request->id);
        $name = DB::table('sub_category')->where('id',$id)->get(['sub_cat_name','icon'])->first();
        return view('update_sub_cat',['id'=>$id,'name'=>$name]);
    }
    function delete_sub_cat(Request $request){
        $id = $request->id;
        DB::table('sub_category')->where('id',$id)->delete();
        return redirect('/categories');
    }
    function delete_user(Request $request, $user_id = 0){

        return redirect('/users');


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


        return redirect('/users');
    }
}
