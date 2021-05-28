<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Sub_category extends Model
{
    protected $table = 'sub_category';
    public function sub_category(){
        return $this->belongsTo(category::class,'id');
    }
    public function sub_cat_name(){
        return $this->belongsTo('App\Model\UserInterests','interest_type');
    }
}
