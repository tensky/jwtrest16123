<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function socialmedias()
    {
        return $this->hasMany('App\SocialMedia');
    }

    protected $fillable = [
        'nama', 'email', 'password',
    ];
}