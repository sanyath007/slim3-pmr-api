<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use App\Models\Visit;

class VisitController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');
        $model = Visit::orderBy('visit_date');
        $visits = paginate($model, 10, $page, $request);
        
        $data = json_encode($visits, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $visit = Visit::where('vn', $args['vn'])->first();
                    
        $data = json_encode($visit, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getPatientVisits($request, $response, $args)
    {
        $visit = Visit::where('patient_hn', $args['hn'])->first();
                    
        $data = json_encode($visit, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        /** ================ Example using ramsey/uuid for vn value ================ */
        $myuuid = Uuid::uuid4();
        printf("Your UUID is: %s", $myuuid->toString());
        /** ================ Example using ramsey/uuid for vn value ================ */

        /** ================ Vilidate request data ================ */
        $this->validator->validate($request, [
            'visit_date'    => v::date()->notEmpty(),
            'visit_time'    => v::time()->notEmpty(),
            'patient_hn'    => v::stringType()->notEmpty(),
            'visit_right'    => v::numeric()->notEmpty(),
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

            $visit = new Visit();
            $visit->vn = Uuid::uuid4();
            $visit->visit_date = $post['visit_date'];
            $visit->visit_time = $post['visit_time'];
            $visit->patient_hn = $post['patient_hn'];
            $visit->visit_right = $post['visit_right'];
            $visit->hosp_main = $post['hosp_main'];
            $visit->hosp_sub = $post['hosp_sub'];
            $visit->pdx = $post['pdx'];
            $visit->total_amt = $post['total_amt'];
            $visit->is_admit = $post['is_admit'];

            if($visit->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'visit' => $visit
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
