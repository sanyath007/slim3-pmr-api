<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Patient;
use App\Models\AdmitAppointment;
use App\Models\Right;
use App\Models\Doctor;
use App\Models\Room;

class AdmitAppointmentController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $appointments = AdmitAppointment::with(['patient' => function($q) {
                            $q->select('id','hn','pname','fname','lname','cid','tel1');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->with(['doctor' => function($q) {
                            $q->select('emp_id', 'title', 'license_no');
                        }])
                        ->with(['doctor.employee' => function($q) {
                            $q->select('id', 'prefix', 'fname', 'lname');
                        }])
                        ->orderBy('admdate')
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
                            ->with(['right' => function($q) {
                                $q->select('id', 'right_name');
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
        $sql = "SELECT admdate, COUNT(id) AS num
                FROM admit_appointments 
                GROUP BY admdate 
                ORDER BY admdate";
        $admits = DB::select($sql);
        $data = json_encode($admits, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'rights'        => Right::all(),
            'doctors'       => Doctor::with('employee')->get(),
            'rooms'         => Room::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            // TODO: should check duplicated patient data before store to db
            $patient = Patient::where('hn', $post['hn'])->orWhere('cid', $post['cid'])->first();

            if ($patient) {
                $admit = new AdmitAppointment;
                $admit->patient         = $patient->id;
                $admit->patient_right   = $post['patient_right'];
                $admit->admdate         = thdateToDbdate($post['admdate']);
                $admit->dchdate         = thdateToDbdate($post['dchdate']);
                // $admit->room            = $post['room'];
                $admit->doctor          = $post['doctor'];
                $admit->admit_for       = $post['admit_for'];
                $admit->diag_text       = $post['diag_text'];
                $admit->admit_user      = $post['user'];
                $admit->status          = 0; // 0=รอดำเนินการ, 1=ตอบรับแล้ว, 2=ตรวจแล้ว, 3=ยกเลิกนัด
                $admit->save();

                /** สร้างไฟล์ใบนัด */
                // $this->createAppointForm($admit->id);

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Inserting successfully',
                            'admit'     => $admit
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }

            $patient = new Patient;
            $patient->hn            = $post['hn'];
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
                $admit = new AdmitAppointment;
                $admit->patient         = $patient->id;
                $admit->patient_right   = $post['patient_right'];
                $admit->admdate         = thdateToDbdate($post['admdate']);
                $admit->dchdate         = thdateToDbdate($post['dchdate']);
                // $admit->room            = $post['room'];
                $admit->doctor          = $post['doctor'];
                $admit->admit_for       = $post['admit_for'];
                $admit->diag_text       = $post['diag_text'];
                $admit->admit_user      = $post['user'];
                $admit->status          = 0; // 0=รอดำเนินการ, 1=ตอบรับแล้ว, 2=ตรวจแล้ว, 3=ยกเลิกนัด
                $admit->save();

                /** สร้างไฟล์ใบนัด */
                // $this->createAppointForm($admit->id);

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Inserting successfully',
                            'admit'     => $admit
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

            $admit = AdmitAppointment::find($args['id']);
            $admit->patient_hn      = $post['hn'];
            $admit->patient_right   = $post['patient_right'];
            $admit->admdate         = thdateToDbdate($post['admdate']);
            $admit->dchdate         = thdateToDbdate($post['dchdate']);
            // $admit->room            = $post['room'];
            $admit->doctor          = $post['doctor'];
            $admit->admit_for       = $post['admit_for'];
            $admit->diag_text       = $post['diag_text'];
            $admit->admit_user      = $post['user'];
            // $admit->status          = 0; // 0=รอดำเนินการ, 1=ตอบรับแล้ว, 2=ตรวจแล้ว, 3=ยกเลิกนัด

            if($admit->save()) {
                /** สร้างไฟล์ใบนัด */
                // $this->createAppointForm($admit->id);

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Updating successfully',
                            'admit'     => $admit
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

    private function createAppointForm($id)
    {
        $appointment = Appointment::with(['patient' => function($q) {
                            $q->select('id','hn','pname','fname','lname','cid','tel1','sex','birthdate');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic_name');
                        }])
                        ->with(['clinic.room' => function($q) {
                            $q->select('id', 'room_name', 'room_tel1');
                        }])
                        ->with(['doctor' => function($q) {
                            $q->select('emp_id', 'title', 'license_no');
                        }])
                        ->with(['doctor.employee' => function($q) {
                            $q->select('id', 'prefix', 'fname', 'lname');
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
                        ->with(['hosp' => function($q) {
                            $q->select('hospcode', 'name', 'hospital_phone');
                        }])
                        ->where('id', $id)
                        ->first();

        $building = $appointment['relations']['clinic']->id == '9' ? 'อาคาร M Park' : 'อาคารผู้ป่วยนอก';
        $appointTime = $appointment->appoint_date == '1' ? '08.00 - 12.00 น.' : '12.00 - 16.00 น.';
        $doctor = $appointment['relations']['clinic']->id == 9 ? '' : '
                    <p>นัดพบ <span>' .$appointment['relations']['doctor']->title.$appointment['relations']['doctor']['relations']['employee']->fname. ' ' .$appointment['relations']['doctor']['relations']['employee']->lname. '</span></p>
                ';
        $before = '';

        if ($appointment['relations']['clinic']->id == 9) {
            $before = '<p><span>-</span></p>';
        } else {
            $before = '
                <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>EKG (ตรวจคลื่นไฟฟ้าหัวใจ)</span>
                </div>
            </div>
            <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>Chest X-Ray (ทำ X-Ray หน้าอก)</span>
                </div>
            </div>
            <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>ไม่ต้องงดน้ำงดอาหารก่อนมาตรวจ</span>
                </div>
            </div>
            ';
        }

        $stylesheet = file_get_contents('assets/css/styles.css');
        $content = '
            <div class="container">
                <div class="header">
                    <div class="header-img">
                        <img src="assets/img/logo_mnrh_512x512.jpg" width="100%" height="100" />
                    </div>
                    <div class="header-text">
                        <h1>ใบนัดตรวจ' .$appointment['relations']['clinic']->clinic_name. '</h1>
                        <h2>โรงพยาบาลมหาราชนครราชสีมา</h2>
                    </div>
                </div>
                <div class="content">
                    <div class="left__content-container">
                        <div class="left__content-patient">
                            <p>เลขที่ใบส่งตัว <span>' .$appointment->refer_no. '</span></p>
                            <p>เลขที่บัตรประชาชน <span>' .$appointment['relations']['patient']->cid. '</span></p>
                            <p>ชื่อ-สกุล <span>
                                ' .$appointment['relations']['patient']->pname.$appointment['relations']['patient']->fname. ' '.$appointment['relations']['patient']->lname. '
                            </span></p>
                            <p>โทรศัพท์ <span>' .$appointment['relations']['patient']->tel1. '</span></p>
                            <p>สิทธิการรักษา <span>' .$appointment['relations']['right']->right_name. '</span></p>
                            <p>ผลการวินิจฉัย <span>' .$appointment['relations']['diag']->name. '</span></p>
                        </div>
                        <div class="left__content-before">
                            <p>การปฎิบัติก่อนมา</p>
                            ' .$before. '
                        </div>
                    </div>
                    <div class="right__content-container">
                        <div class="right__content-appoint">
                            ' .$doctor. '
                            <p>วันนัด <span>' .$appointment->appoint_date. '</span></p>
                            <p>เวลา <span>' .$appointTime. '</span></p>
                            </div>
                        <div class="right__content-clinic">
                            <p>ยื่นใบนัดที่ <span>' .$appointment['relations']['clinic']['relations']['room']->room_name. '</span></p>
                            <p><span>' .$building. '</span></p>
                            <p>หมายเลขโทรศัพท์ <span>' .$appointment['relations']['clinic']['relations']['room']->room_tel1. '</span></p>
                        </div>
                        <div class="right__content-remark">
                            <p>หมายเหตุ : <span>กรณีไม่สามารถมาตามนัดได้ หรือต้องการเลื่อนนัด ให้ติดต่อที่โรงพยาบาลที่ทำการออกใบนัด</span></p>
                        </div>
                    </div>
                    <div class="bottom-content">
                        <p>ขั้นตอนการรับบริการ</p>
                        <ul>
                            <li>1. ยื่นใบนัด / ใบส่งตัว (ออกจากระบบ R9Refer เท่านั้น) <span class="text-underline">ที่' .$appointment['relations']['clinic']['relations']['room']->room_name. '</span></li>
                            <li>2. ชั่งน้ำหนัก วัดความดันโลหิต</li>
                            <li>3. รอพยาบาลเรียกซักประวัติ</li>
                            <li>4. พบแพทย์</li>
                            <li>5. พบพยาบาลหลังตรวจ รับใบสั่งยา และ / หรือ ใบนัดครั้งต่อไป</li>
                        </ul>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-header">
                        <p>หมายเหตุ : <span>กรณีไม่สามารถมาตามนัดได้ หรือต้องการเลื่อนนัด ให้ติดต่อที่โรงพยาบาลที่ออกใบนัด</span></p>
                    </div>
                    <div class="footer-content">
                        <div class="left-footer">
                            <p>ผู้ลงเวลานัด <span>-</span></p>
                            <p>ผู้พิมพ์ใบนัด <span>-</span></p>
                            <p>วัน/เวลา ที่ลงนัด <span>' .$appointment->created_at. '</span></p>
                        </div>
                        <div class="right-footer">
                            <p>สถานพยาบาลออกใบส่งตัว</p>
                            <p><span>' .$appointment['relations']['hosp']->name. '</span></p>
                            <p>โทรศัพท์ <span>044395000 ต่อ 2510</span></p>
                        </div>
                    </div>
                </div>
            </div>
        ';

        // TODO: should create pdf file with generated unduplicate name for avoid browser caching
        $filename = APP_ROOT_DIR . '/public/downloads/' .$appointment->id. '.pdf';

        $this->generatePdf($stylesheet, $content, $filename);
    }

    private function generatePdf($stylesheet, $content, $path)
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

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output($path, 'F');
    }
}
