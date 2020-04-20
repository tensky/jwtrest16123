<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $table = 'social_media';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    protected $fillable = [
        'social_media', 'username', 'user_id'
    ];
}