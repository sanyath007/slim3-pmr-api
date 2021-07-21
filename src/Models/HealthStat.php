<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthStat extends Model
{
    protected $table = "health_stats";

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_hn', 'hn');
    }
}