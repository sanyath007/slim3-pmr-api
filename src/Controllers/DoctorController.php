<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Doctor;
use App\Models\Employee;

class DoctorController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $doctors = Doctor::with('employee', 'employee.position', 'employee.positionClass', 'employee.positionType')
                    ->with('depart')
                    ->get();
        
        $data = json_encode($doctors, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getUser($request, $response, $args)
    {
        $doctor = Doctor::where('emp_id', $args['id'])
                    ->with('employee', 'employee.position', 'employee.positionClass', 'employee.positionType')
                    ->with('depart')
                    ->first();
                    
        $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $post = (array)$request->getParsedBody();
        $employee = new Employee;
        $employee->cid          = $post['cid'];
        $employee->patient_hn   = $post['patient_hn'];
        $employee->prefix       = $post['prefix'];
        $employee->fname        = $post['fname'];
        $employee->lname        = $post['lname'];
        $employee->sex          = $post['sex'];
        $employee->birthdate    = $post['birthdate'];
        $employee->position     = $post['position'];
        $employee->start_date   = $post['start_date'];

        if ($employee->save()) {
            $doctor = new Doctor;
            $doctor->emp_id                 = $employee->id;
            $doctor->title                  = $post['title'];
            $doctor->license_no             = $post['license_no'];
            $doctor->license_renewal_date   = $post['license_renewal_date'];
            $doctor->depart                 = $post['depart'];
            $doctor->save();

            $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write($data);
        } else {
            // Throw error exeption
        }
    }

    public function update($request, $response, $args)
    {
        $post = (array)$request->getParsedBody();
        $employee = Employee::find($args['id']);
        $employee->cid          = $post['cid'];
        $employee->patient_hn   = $post['patient_hn'];
        $employee->prefix       = $post['prefix'];
        $employee->fname        = $post['fname'];
        $employee->lname        = $post['lname'];
        $employee->sex          = $post['sex'];
        $employee->birthdate    = $post['birthdate'];
        $employee->position     = $post['position'];
        $employee->start_date   = $post['start_date'];

        if ($employee->save()) {
            $doctor = Doctor::where('emp_id', $args['id']);
            $doctor->title                  = $post['title'];
            $doctor->license_no             = $post['license_no'];
            $doctor->license_renewal_date   = $post['license_renewal_date'];
            $doctor->depart                 = $post['depart'];
            $doctor->save();
            
            $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write($data);
        } else {
            // Throw error exeption
        }
    }

    public function delete()
    {

    }
}
