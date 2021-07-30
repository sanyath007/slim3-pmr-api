<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "users";

    public function permissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id');
    }
}