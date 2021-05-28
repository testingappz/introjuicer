<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    public function images(){
	    return $this->belongsTo('App\Model\Group', 'group_id', 'id');
	}
}
