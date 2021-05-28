<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    protected $table = 'jobs';
    public function users(){
        // return $this->belongsTo('App\Model\Users');
        return $this->hasOne('App\Model\Users','id','user_id');
    }
}
