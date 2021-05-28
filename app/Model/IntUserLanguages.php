<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IntUserLanguages extends Model
{
    protected $table = 'int_user_languages';
    protected $fillable = [
       'user_id','language'
    ];
    protected $hidden = [
       // 'remember_token',
    ];

    public function user(){
	    return $this->belongsTo('App\Model\Users', 'user_id', 'id');
	}
}
