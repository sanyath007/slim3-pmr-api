<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = "employees";

    public function position()
    {
        return $this->belongsTo(Position::class, 'position', 'id');
    }

    public function positionClass()
    {
        return $this->belongsTo(PositionClass::class, 'position_class', 'id');
    }

    public function positionType()
    {
        return $this->belongsTo(PositionType::class, 'position_type', 'id');
    }
}