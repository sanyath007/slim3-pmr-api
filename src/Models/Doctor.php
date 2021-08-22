<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = "doctors";

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'id', 'doctor');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'emp_id', 'id');
    }

    public function depart()
    {
        return $this->belongsTo(Department::class, 'depart', 'id');
    }
}