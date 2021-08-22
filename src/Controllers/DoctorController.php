<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $doctors = Doctor::all();
        
        $data = json_encode($doctors, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getUser($request, $response, $args)
    {
        $doctor = Doctor::where('emp_id', $args['id'])
                    ->get()
                    ->first();
                    
        $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
}
