<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $table = 'categories';
    public function categories(){
        return $this->hasMany(Sub_category::class,'cat_id');
    }
}
