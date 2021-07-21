<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $table = "nationalities";

    // public function patients()
    // {
    //     return $this->hasMany(Patient::class, 'id', 'right');
    // }
}