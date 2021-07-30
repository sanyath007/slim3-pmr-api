<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = "user_permissions";

    public function role()
    {
        return $this->belongsTo(PermissionRole::class, 'role', 'id');
    }
}