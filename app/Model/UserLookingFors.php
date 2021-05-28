<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserLookingFors extends Model
{
    protected $table = 'user_looking_fors';
    protected $fillable = [
       'user_id','looking_for'
    ];
    protected $hidden = [
       // 'remember_token',
    ];

    public function user(){
	    return $this->belongsTo('App\Model\Users', 'user_id', 'id');
	}
}
