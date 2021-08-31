<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Right extends Model
{
    protected $table = "rights";

    public function patients()
    {
        return $this->hasMany(Patient::class, 'id', 'main_right');
    }
}