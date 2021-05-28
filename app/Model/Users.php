<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'users';
    protected $fillable = [
       'id','name','first_name','last_name','gender','dob','age','email','email_verified_at','phone_number','password','facebook_id','instagram_id','profession','relationship_status','visibility','religion','family','body_shape','height','auth_token','remember_token','mail_token','deleted_at'
    ];
    protected $hidden = [
       // 'remember_token',
    ];

    public function userInterest(){
	    return $this->hasMany('App\Model\UserInterests', 'user_id', 'id');
	}

	public function userLookingFor(){
	    return $this->hasMany('App\Model\UserLookingFors', 'user_id', 'id');
	}

	public function userLanguage(){
	    return $this->hasMany('App\Model\IntUserLanguages', 'user_id', 'id');
    }
    public function groupUsers(){
        return $this->hasMany('App\Model\Group_user', 'group_id','id');
    }
    public function jobs(){
        // return $this->hasOne('App\Model\Jobs','user_id');
        return $this->belongsTo('App\Model\Jobs','user_id','id');
    }
    public function sell(){
        // return $this->hasOne('App\Model\Jobs','user_id');
        return $this->belongsTo('App\Model\Sell','user_id','id');
    }
}
