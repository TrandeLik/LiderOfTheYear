<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'achievements';

    public function user(){
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function comments(){
        return $this->hasMany('App\Comment', 'achievement_id', 'id');
    }


}
