<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Model\Users;
class Changepasswordcontroller extends Controller
{
    public function __invoke(Request $request){
        if(!isset($request->user_id)){
			echo json_encode(array('success'=>0,'message'=>'Please provide an id.'));
			exit;
		}
		if(!isset($request->password)){
			echo json_encode(array('success'=>0,'message'=>'Please provide an password.'));
			exit;
		}
		$user = Users::where('id', $request->user_id)->first();
        $user->password = Hash::make($request->password);
		$user->save();
		echo json_encode(array('status'=>'Password changed successfully.','error'=>0,'message'=>'Password changed successfully.'));
    }
}
