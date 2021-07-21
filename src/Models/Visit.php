<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $table = "visits";

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_hn', 'hn');
    }

    public function patient_right()
    {
        return $this->belongsTo(Patient::class, 'patient_hn', 'right');
    }
}