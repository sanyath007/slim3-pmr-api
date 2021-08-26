<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DiagGroup;
use App\Models\ReferCause;
use App\Models\Right;

class AppointmentController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $appointments = Appointment::with(['patient' => function($q) {
                            $q->select('id','hn','pname','fname','lname','cid','tel1');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic_name');
                        }])
                        ->with(['diag' => function($q) {
                            $q->select('id', 'name');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->orderBy('appoint_date')
                        ->get();
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $appointment    = Appointment::with(['patient' => function($q) {
                                $q->select('id','hn','pname','fname','lname','cid','tel1','sex','birthdate');
                            }])
                            ->with(['clinic' => function($q) {
                                $q->select('id', 'clinic_name');
                            }])
                            ->with(['diag' => function($q) {
                                $q->select('id', 'name');
                            }])
                            ->with(['right' => function($q) {
                                $q->select('id', 'right_name');
                            }])
                            ->with(['referCause' => function($q) {
                                $q->select('id', 'name');
                            }])
                            ->where('id', $args['id'])
                            ->first();

        $data = json_encode($appointment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

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

    public function getCountByDate($request, $response, $args)
    {
        $sql = "SELECT appoint_date, COUNT(id) AS num
                FROM appointments 
                GROUP BY appoint_date 
                ORDER BY appoint_date";
        $appointments = DB::select($sql);
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'clinics'       => Clinic::all(),
            'diagGroups'    => DiagGroup::all(),
            'referCauses'    => ReferCause::all(),
            'rights'        => Right::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        // $this->validator->validate($request, [
        //     'patient_hn'    => v::numeric(),
        //     'cid'           => v::numeric(),
        //     'pname'         => v::numeric(),
        //     'fname'         => v::numeric(),
        //     'lname'         => v::numeric(),
        //     'appoint_date'  => v::stringType()->notEmpty(),
        //     'appoint_time'  => v::stringType()->notEmpty(),
        //     'clinic_id'     => v::stringType()->notEmpty(),
        //     'diag_group'    => v::stringType()->notEmpty(),
        //     'refer_no'      => v::stringType()->notEmpty(),
        //     'refer_cause'   => v::stringType()->notEmpty(),
        // ]);

        // if ($this->validator->failed()) {
        //     return $response
        //                 ->withStatus(200)
        //                 ->withHeader("Content-Type", "application/json")
        //                 ->write(json_encode([
        //                     'status' => 0,
        //                     'message' => 'Data Invalid !!',
        //                     'errors' => $this->validator->getMessages()
        //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        // }

        // TODO: should check duplicated patient data before store to db
        try {
            $post = (array)$request->getParsedBody();

            if (!empty($post['patient_id'])) {
                $appointment = new Appointment;
                $appointment->patient       = $post['patient_id'];
                $appointment->patient_right = $post['patient_right'];
                $appointment->appoint_date  = thdateToDbdate($post['appoint_date']);
                $appointment->appoint_time  = $post['appoint_time'];
                $appointment->appoint_type  = $post['appoint_type'];
                $appointment->clinic        = $post['clinic'];
                $appointment->doctor        = $post['doctor'];
                $appointment->diag_group    = $post['diag_group'];
                $appointment->diag_text     = $post['diag_text'];
                $appointment->refer_no      = $post['refer_no'];
                $appointment->refer_cause   = $post['refer_cause'];
                $appointment->hospcode      = $post['hospcode'];
                $appointment->appoint_user  = $post['user'];
                $appointment->status        = 0; // 0=รอดำเนินการ, 1=ตอบรับแล้ว, 2=ตรวจแล้ว, 3=ยกเลิกนัด
                $appointment->save();

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'appointment' => $appointment
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }

            $patient = new Patient;
            $patient->hn            = $post['patient_hn'];
            $patient->cid           = $post['cid'];
            $patient->passport      = $post['passport'];
            $patient->pname         = $post['pname'];
            $patient->fname         = $post['fname'];
            $patient->lname         = $post['lname'];
            $patient->sex           = $post['sex'];
            $patient->birthdate     = thdateToDbdate($post['birthdate']);
            $patient->tel1          = $post['tel1'];
            $patient->tel2          = $post['tel2'];
            $patient->tel2          = $post['tel2'];
            $patient->main_right    = $post['patient_right'];
            
            if($patient->save()) {
                $appointment = new Appointment;
                $appointment->patient       = $patient->id;
                $appointment->patient_right = $post['patient_right'];
                $appointment->appoint_date  = thdateToDbdate($post['appoint_date']);
                $appointment->appoint_time  = $post['appoint_time'];
                $appointment->appoint_type  = $post['appoint_type'];
                $appointment->clinic        = $post['clinic'];
                $appointment->doctor        = $post['doctor'];
                $appointment->diag_group    = $post['diag_group'];
                $appointment->diag_text     = $post['diag_text'];
                $appointment->refer_no      = $post['refer_no'];
                $appointment->refer_cause   = $post['refer_cause'];
                $appointment->hospcode      = $post['hospcode'];
                $appointment->appoint_user  = $post['user'];
                $appointment->status        = 0; // 0=รอดำเนินการ, 1=ตอบรับแล้ว, 2=ตรวจแล้ว, 3=ยกเลิกนัด
                $appointment->save();

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

    public function getPdf($request, $response, $args)
    {
        $this->generatePdf();
    }

    private function generatePdf()
    {
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                APP_ROOT_DIR . '/public/assets/fonts',
            ]),
            'fontdata' => $fontData + [
                    'sarabun' => [
                        'R' => 'THSarabunNew.ttf',
                        'I' => 'THSarabunNew Italic.ttf',
                        'B' => 'THSarabunNew Bold.ttf',
                    ]
                ],
        ]);

        $text = "ภาษาไทย หรือ ภาษาไทยกลาง เป็นภาษาราชการและภาษาประจำชาติของประเทศไทย ภาษาไทยเป็นภาษาในกลุ่มภาษาไท ซึ่งเป็นกลุ่มย่อยของตระกูลภาษาไท-กะได สันนิษฐานว่า ภาษาในตระกูลนี้มีถิ่นกำเนิดจากทางตอนใต้ของประเทศจีน และนักภาษาศาสตร์บางส่วนเสนอว่า ภาษาไทยน่าจะมีความเชื่อมโยงกับตระกูลภาษาออสโตร-เอเชียติก ตระกูลภาษาออสโตรนีเซียน และตระกูลภาษาจีน-ทิเบต
                ภาษาไทยเป็นภาษาที่มีระดับเสียงของคำแน่นอนหรือวรรณยุกต์เช่นเดียวกับภาษาจีน และออกเสียงแยกคำต่อคำ
                ภาษาไทยปรากฏครั้งแรกในพุทธศักราช 1826 โดยพ่อขุนรามคำแหง และปรากฏอย่างสากลและใช้ในงานของราชการ เมื่อวันที่ 31 มีนาคม พุทธศักราช 2476 ด้วยการก่อตั้งสำนักงานราชบัณฑิตยสภาขึ้น และปฏิรูปภาษาไทย พุทธศักราช 2485
                คำว่า ไทย หมายความว่า อิสรภาพ เสรีภาพ หรืออีกความหมายหนึ่งคือ ใหญ่ ยิ่งใหญ่ เพราะการจะเป็นอิสระได้จะต้องมีกำลังที่มากกว่า แข็งแกร่งกว่า เพื่อป้องกันการรุกรานจากข้าศึก คำนี้เป็นคำไทยแท้ที่เกิดจากการสร้างคำที่เรียก \"การลากคำเข้าวัด\" ซึ่งเป็นการลากความวิธีหนึ่ง ตามหลักคติชนวิทยา คนไทยเป็นชนชาติที่นับถือกันว่า ภาษาบาลี ซึ่งเป็นภาษาที่บันทึกพระธรรมคำสอนของพระพุทธเจ้าเป็นภาษาอันศักดิ์สิทธิ์และเป็นมงคล เมื่อคนไทยต้องการตั้งชื่อประเทศว่า ไท ซึ่งเป็นคำไทยแท้ จึงเติมตัว ย เข้าไปข้างท้าย เพื่อให้มีลักษณะคล้ายคำในภาษาบาลี – สันสกฤตเพื่อความเป็นมงคลตามความเชื่อของตน ภาษาไทยจึงหมายถึงภาษาของชนชาติไทยผู้เป็นไทนั่นเอง
                พ่อขุนรามคำแหงได้ทรงประดิษฐ์อักษรไทยขึ้นเมื่อปี พ. ศ. 1826 มี พยัญชนะ 44 ตัว (21 เสียง), สระ 21 รูป (32 เสียง), วรรณยุกต์ 5 เสียง คือ เสียง สามัญ เอก โท ตรี จัตวา ภาษาไทยดัดแปลงมาจากบาลี-สันสกฤต มอญ และ เขมร";

        $content = '
            <style>
                .container{
                    font-family: "sarabun";
                    font-size: 12pt;
                }
                p{
                    text-align: justify;
                }
                h1{
                    text-align: center;
                }
            </style>
            <div class="container" style="width: 50%">
                <h1>ภาษาไทย</h1>
                <p>'.$text.'</p>
            </div>
        ';

        $mpdf->WriteHTML($content);
        $mpdf->Output(APP_ROOT_DIR . '/public/downloads/test.pdf', 'F');
    }
}
