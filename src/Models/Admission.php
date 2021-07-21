<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $table = "admissions";

    // public function patients()
    // {
    //     return $this->hasMany(Patient::class, 'id', 'right');
    // }
}