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
}