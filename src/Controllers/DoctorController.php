<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\DoctorSpecialist;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\PositionClass;
use App\Models\Department;
use App\Models\Specialist;

class DoctorController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $doctors = Doctor::with('employee', 'employee.position', 'employee.positionClass', 'employee.positionType')
                    ->with('depart', 'specialists', 'specialists.specialist')
                    ->get();
        
        $data = json_encode($doctors, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $doctor = Doctor::where('emp_id', $args['id'])
                    ->with('employee', 'employee.position', 'employee.positionClass', 'employee.positionType')
                    ->with('depart', 'specialists', 'specialists.specialist')
                    ->first();
                    
        $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getDortorsOfClinic($request, $response, $args)
    {
        // TODO: to response doctor that have fewest appointments
        $sql = "select d.emp_id, count(a.id) as amt	
                from appointment_online_db.doctors d
                left join appointment_online_db.appointments a on (d.emp_id=a.doctor)
                where (d.emp_id in (select doctor from appointment_online_db.doctor_specialists where specialist=?))
                and (d.status='1') #1=อยู่,2=ลาศึกษาต่อ,3=ลาคลอด,4=โอน/ย้าย,5=ลาออก
                group by d.emp_id
                order by count(a.id) ASC";
        $doctor_count = collect(DB::select($sql, [$args['specialist']]))->first();

        $doctor = Doctor::where('emp_id', $doctor_count->emp_id)
                    ->with('employee', 'employee.position', 'employee.positionClass', 'employee.positionType')
                    ->with('depart', 'specialists', 'specialists.specialist')
                    ->first();

        $data = json_encode($doctor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'positions'       => Position::all(),
            'positionClasses' => PositionClass::all(),
            'positionTypes'   => PositionType::all(),
            'departs'         => Department::all(),
            'specialists'     => Specialist::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $employee = new Employee;
            $employee->cid              = $post['cid'];
            $employee->patient_hn       = $post['patient_hn'];
            $employee->prefix           = $post['prefix'];
            $employee->fname            = $post['fname'];
            $employee->lname            = $post['lname'];
            $employee->sex              = $post['sex'];
            $employee->birthdate        = thdateToDbdate($post['birthdate']);
            $employee->position         = $post['position'];
            $employee->position_class   = $post['position_class'];
            $employee->position_type    = $post['position_type'];
            $employee->start_date       = thdateToDbdate($post['start_date']);

            if ($employee->save()) {
                $doctor = new Doctor;
                $doctor->emp_id                 = $employee->id;
                $doctor->title                  = $post['title'];
                $doctor->license_no             = $post['license_no'];
                $doctor->license_renewal_date   = thdateToDbdate($post['license_renewal_date']);
                $doctor->depart                 = $post['depart'];
                $doctor->remark                 = $post['remark'];
                $doctor->save();

                /** Update doctor specialist table */
                $specialist = new DoctorSpecialist;
                $specialist->doctor     = $employee->id;
                $specialist->specialist    = $post['specialist'];
                $specialist->save();

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Inserting successfully',
                            'doctor'    => $doctor
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $employee = Employee::find($args['id']);
            $employee->cid              = $post['cid'];
            $employee->patient_hn       = $post['patient_hn'];
            $employee->prefix           = $post['prefix'];
            $employee->fname            = $post['fname'];
            $employee->lname            = $post['lname'];
            $employee->sex              = $post['sex'];

            if (array_key_exists('birthdate', $post)) {
                $employee->birthdate        = thdateToDbdate($post['birthdate']);
            }

            $employee->position         = $post['position'];
            $employee->position_class   = $post['position_class'];
            $employee->position_type    = $post['position_type'];

            if (array_key_exists('start_date', $post)) {
                $employee->start_date       = thdateToDbdate($post['start_date']);
            }

            if ($employee->save()) {
                $doctor = Doctor::find($args['id']);
                $doctor->title                  = $post['title'];
                $doctor->license_no             = $post['license_no'];

                if (array_key_exists('license_renewal_date', $post)) {
                    $doctor->license_renewal_date   = thdateToDbdate($post['license_renewal_date']);
                }

                $doctor->depart                 = $post['depart'];
                $doctor->remark                 = $post['remark'];
                $doctor->save();

                /** TODO: Update doctor specialist table */
                // TODO: to update doctor specialist

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Updating successfully',
                            'doctor'    => $doctor
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function delete()
    {
        try {
            $employee = Employee::find($args['id']);

            if ($employee->delete()) {
                $doctor = Doctor::where('emp_id', $args['id'])->delete();

                /** Update doctor specialist table */
                // TODO: to update doctor specialist

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Deleting successfully'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
