<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSpecialist extends Model
{
    protected $table = "doctor_specialists";

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor', 'id');
    }
    
    public function specialist()
    {
        return $this->belongsTo(Specialist::class, 'specialist', 'id');
    }
}
