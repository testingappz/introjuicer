<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class image extends Model
{
    protected $table = 'images';

    public function images(){
	    return $this->belongsTo('App\Model\Group', 'group_id', 'id');
    }
    public function listGroupImages(){
	    return $this->belongsTo('App\Model\Group_user', 'group_id', 'id');
    }
    public function groupImages(){
	    return $this->belongsTo('App\Model\User_favorites', 'ids', 'id');
    }
}
