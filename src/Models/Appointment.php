<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = "appointments";

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_hn', 'hn');
    }
    
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }
    
    public function right()
    {
        return $this->belongsTo(Right::class, 'patient_right', 'id');
    }
    
    public function patients()
    {
        return $this->hasMany(Patient::class, 'hn', 'patient_hn');
    }
}