<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "users";

    protected $hidden = [
        'password',
    ];

    public function permissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}