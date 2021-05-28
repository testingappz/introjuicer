<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Sell extends Model
{
    protected $table = 'sell';
    public function users(){
        // return $this->belongsTo('App\Model\Users');
        return $this->hasOne('App\Model\Users','id','user_id');
    }
}
