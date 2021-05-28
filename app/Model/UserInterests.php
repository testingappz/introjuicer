<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserInterests extends Model
{
    protected $table = 'user_interests';
    protected $fillable = [
       'user_id','interest_type'
    ];
    protected $hidden = [
       // 'remember_token',
    ];
    public function user(){
	    return $this->belongsTo('App\Model\Users', 'user_id', 'id');
   }
   public function interestType(){
      return $this->hasMany('App\Model\Sub_category', 'sub_cat_name');
  }
}
