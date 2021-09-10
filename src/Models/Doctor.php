<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = "doctors";
    protected $primaryKey = "emp_id";
    public $incrementing = false; //ไม่ใช้ options auto increment
    // public $timestamps = false; //ไม่ใช้ field updated_at และ created_at

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor', 'emp_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'emp_id');
    }

    public function depart()
    {
        return $this->belongsTo(Department::class, 'depart', 'id');
    }

    public function specialists()
    {
        return $this->hasMany(DoctorSpecialist::class, 'doctor', 'emp_id');
    }
}