<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    public function groupImages(){
	    return $this->hasMany('App\Model\image', 'group_id', 'id');
    }
    public function groupTags(){
	    return $this->hasMany('App\Model\Tag', 'group_id', 'id');
    }
    public function groupDetail(){
	    return $this->belongsTo('App\Model\User_favorites', 'ids', 'id');
    }
}
