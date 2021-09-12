<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = "appointments";

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient', 'id');
    }

    // public function clinic()
    // {
    //     return $this->belongsTo(Clinic::class, 'clinic', 'id');
    // }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor', 'emp_id');
    }

    // public function diag()
    // {
    //     return $this->belongsTo(DiagGroup::class, 'diag_group', 'id');
    // }

    // public function admitFor()
    // {
    //     return $this->belongsTo(ReferCause::class, 'refer_cause', 'id');
    // }

    public function right()
    {
        return $this->belongsTo(Right::class, 'patient_right', 'id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'hn', 'patient_hn');
    }
}