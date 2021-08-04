<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagGroup extends Model
{
    protected $table = "diag_groups";

    // public function patient()
    // {
    //     return $this->belongsTo(Patient::class, 'patient_hn', 'hn');
    // }
}