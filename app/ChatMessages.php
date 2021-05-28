<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    protected $table = 'int_chat_messages';
    protected $fillable = [
       'id','sender','message','type','additional_detail','receiver'
    ];
    protected $hidden = [
       // 'remember_token',
    ];
}
