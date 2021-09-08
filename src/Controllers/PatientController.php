<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Patient;
use App\Models\BloodGroup;
use App\Models\Right;
use App\Models\Tambon;
use App\Models\Amphur;
use App\Models\Changwat;
use App\Models\Nationality;
use App\Models\Visit;

class PatientController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');

        $patients = Patient::with('right')->orderBy('hn')->get();

        $data = json_encode($patients, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $patient    = Patient::with('right')->where('id', $args['id'])->first();

        $data = json_encode($patient, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getByCid($request, $response, $args)
    {
        $patient    = Patient::with('right')->where('cid', $args['cid'])->first();

        $data = json_encode($patient, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function generateId($request, $response, $args)
    {
        echo Uuid::uuid4();
    }

    public function getFormInit($request, $response, $args)
    {                    
        $data = json_encode([
            'blood_groups'  => BloodGroup::all(),
            'rights'        => Right::all(),
            'tambons'       => Tambon::all(),
            'amphurs'       => Amphur::all(),
            'changwats'     => Changwat::all(),
            'nationalities' => Nationality::orderBy('nhso_code')->get()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $this->validator->validate($request, [
            'hn'            => v::numeric()->length(7, 7),
            'cid'           => v::numeric()->length(13, 13),
            'pname'         => v::stringType()->notEmpty(),
            'fname'         => v::stringType()->notEmpty(),
            'lname'         => v::stringType()->notEmpty(),
            'sex'           => v::numeric()->notEmpty(),
            'birthdate'     => v::date()->notEmpty(),
            'tel1'          => v::stringType()->notEmpty(),
            'address'       => v::stringType()->notEmpty(),
            'tambon'        => v::stringType()->notEmpty(),
            'amphur'        => v::stringType()->notEmpty(),
            'changwat'      => v::stringType()->notEmpty(),
            'zipcode'       => v::numeric()->length(5, 5),
            'nationality'   => v::stringType()->notEmpty(),
            'reg_date'      => v::date()->notEmpty(),
            'line_id'       => v::stringType()->notEmpty(),
            'gmap_url'      => v::stringType()->notEmpty(),
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

            $patient = new Patient;
            $patient->id            = Uuid::uuid4();
            $patient->hn            = $post['hn'];
            $patient->cid           = $post['cid'];
            $patient->pname         = $post['pname'];
            $patient->fname         = $post['fname'];
            $patient->lname         = $post['lname'];
            $patient->sex           = $post['sex'];
            $patient->birthdate     = $post['birthdate'];
            $patient->address       = $post['address'];
            $patient->moo           = $post['moo'];
            $patient->road          = $post['road'];
            $patient->tambon        = $post['tambon'];
            $patient->amphur        = $post['amphur'];
            $patient->changwat      = $post['changwat'];
            $patient->zipcode       = $post['zipcode'];
            $patient->tel1          = $post['tel1'];
            $patient->tel2          = $post['tel2'];
            $patient->main_right    = $post['right'];
            $patient->hosp_main     = $post['hosp_main'];
            $patient->passport      = $post['passport'];
            $patient->nationality   = $post['nationality'];
            $patient->race          = $post['race'];
            $patient->blood_group   = $post['blood_group'];
            $patient->reg_date      = $post['reg_date'];
            $patient->line_id       = $post['line_id'];
            $patient->gmap_url      = $post['gmap_url'];
            $patient->verify_hashed = password_hash($post['tel1'], PASSWORD_BCRYPT);

            /** Upload image */
            $upload_url = 'http://'.$request->getServerParam('SERVER_NAME').$request->getServerParam('PHP_SELF');
            $img_url = uploadImage($post['img_url'], $upload_url);
            $patient->img_url = $img_url;

            if($patient->save()) {
                if (count($post['visits']) > 0) {
                    foreach ($post['visits'] as $vs) {
                        $visit              = new Visit();
                        $visit->vn          = Uuid::uuid4()->toString();
                        $visit->an          = $vs['an'];
                        $visit->visit_date  = $vs['visit_date'];
                        $visit->visit_time  = $vs['visit_time'];
                        $visit->detail      = $vs['detail'];
                        $visit->patient_hn  = $patient->hn;
                        $visit->visit_right = $patient->right;
                        $visit->is_admit    = 1;

                        $visit->save();
                    }
                }

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'patient' => $patient
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

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $patient = Patient::where('hn', $post['hn'])->first();
            $patient->hn = $post['hn'];
            $patient->cid = $post['cid'];
            $patient->pname = $post['pname'];
            $patient->fname = $post['fname'];
            $patient->lname = $post['lname'];
            $patient->sex = $post['sex'];
            $patient->birthdate = $post['birthdate'];
            $patient->address = $post['address'];
            $patient->moo = $post['moo'];
            $patient->road = $post['road'];
            $patient->tambon = $post['tambon'];
            $patient->amphur = $post['amphur'];
            $patient->changwat = $post['changwat'];
            $patient->zipcode = $post['zipcode'];
            $patient->tel1 = $post['tel1'];
            $patient->tel2 = $post['tel2'];
            $patient->right = $post['right'];
            // $patient->hosp_main = $post['hosp_main'];
            // $patient->passport = $post['passport'];
            $patient->nationality = $post['nationality'];
            // $patient->race = $post['race'];
            $patient->blood_group = $post['blood_group'];
            $patient->reg_date = $post['reg_date'];
            $patient->line_id = $post['line_id'];
            $patient->gmap_url = $post['gmap_url'];
            $patient->verify_hashed = password_hash($post['tel1'], PASSWORD_BCRYPT);

            /** Upload image */
            $upload_url = 'http://'.$request->getServerParam('SERVER_NAME').$request->getServerParam('PHP_SELF');
            $img_url = uploadImage($post['img_url'], $upload_url);
            $patient->img_url = $img_url;

            if($patient->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'patient' => $patient
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
