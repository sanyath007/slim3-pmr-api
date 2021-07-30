<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $appointments = Appointment::with(['patient' => function($q) {
                            $q->select('hn', 'pname', 'fname', 'lname', 'cid', 'tel1');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->get();
        
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getAppointmentsByPatient($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');
        $model = Appointment::where('patient_hn', $args['hn'])
                    ->orderBy('stat_date', 'DESC')
                    ->orderBy('stat_time', 'DESC');
        $appointments = paginate($model, 10, $page, $request);

        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $appointment    = Appointment::with('right', 'blood_group', 'drug_allergies')
                        ->where('hn', $args['hn'])
                        ->first();
                    
        $data = json_encode($appointment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $this->validator->validate($request, [
            'temp'      => v::numeric(),
            'o2'        => v::numeric(),
            'bps'       => v::numeric(),
            'bpd'       => v::numeric(),
            'pr'        => v::numeric(),
            'detail'    => v::stringType()->notEmpty(),
        ]);

        if ($this->validator->failed()) {
            return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 0,
                            'message' => 'Data Invalid !!',
                            'errors' => $this->validator->getMessages()
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }

        try {
            $post = (array)$request->getParsedBody();

            $appointment = new Appointment;
            $appointment->patient_hn = $post['patient_hn'];
            $appointment->stat_date  = $post['stat_date'];
            $appointment->stat_time  = $post['stat_time'];
            // $appointment->weight  = $post['weight'];
            // $appointment->height  = $post['height'];
            $appointment->temp       = $post['temp'];
            $appointment->o2         = $post['o2'];
            $appointment->bps        = $post['bps'];
            $appointment->bpd        = $post['bpd'];
            $appointment->pr         = $post['pr'];
            $appointment->detail     = $post['detail'];
            $appointment->remark     = $post['remark'];

            if($appointment->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'appointment' => $appointment
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status' => 0,
                        'message' => 'Something went wrong!!'
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status' => 0,
                        'message' => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
