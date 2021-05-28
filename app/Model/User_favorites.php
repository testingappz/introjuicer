<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User_favorites extends Model
{
    protected $table = 'user_favorites';

    public function groupDetail(){
	    return $this->hasMany('App\Model\Group', 'id', 'id');
    }
    public function groupImages(){
	    return $this->hasMany('App\Model\image', 'group_id', 'id');
    }
}
