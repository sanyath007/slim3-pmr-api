<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $table = "clinics";

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'id', 'clinic');
    }
}