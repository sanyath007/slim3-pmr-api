<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodGroup extends Model
{
    protected $table = "blood_groups";

    public function patients()
    {
        return $this->hasMany(Patient::class, 'blood_id', 'blood_group');
    }
}