<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferCause extends Model
{
    protected $table = "refer_causes";

    // public function patient()
    // {
    //     return $this->belongsTo(Patient::class, 'patient_hn', 'hn');
    // }
}