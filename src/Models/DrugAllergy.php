<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugAllergy extends Model
{
    protected $table = "drug_allergies";

    public function patients()
    {
        return $this->belongsTo(Patient::class, 'hn', 'patient_hn');
    }
}