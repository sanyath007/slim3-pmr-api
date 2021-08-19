<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = "patients";

    public function right()
    {
        return $this->belongsTo(Right::class, 'right', 'id');
    }
    
    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group', 'blood_id');
    }
    
    // public function drug_allergies()
    // {
    //     return $this->hasMany(DrugAllergy::class, 'patient_hn', 'hn');
    // }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient', 'id');
    }
}