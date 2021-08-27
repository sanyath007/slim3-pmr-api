<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = "rooms";

    public function clinic()
    {
        return $this->hasMany(Clinic::class, 'id', 'room_id');
    }
}