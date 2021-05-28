<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group_user extends Model
{
    protected $table = 'group_users';

    public function groupUsers(){
	    return $this->belongsTo('App\Model\Users', 'user_id','id');
    }
    public function listGroups(){
	    return $this->hasMany('App\Model\Group', 'id', 'id');
    }
    public function listGroupImages(){
	    return $this->hasMany('App\Model\image', 'group_id', 'id');
    }
}
