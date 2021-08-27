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

        $stylesheet = file_get_contents('assets/css/styles.css');
        $content = '
            <div class="container">
                <div class="header">
                    <div class="header-img">
                        <img src="assets/img/logo_mnrh_512x512.jpg" width="100%" height="100" />
                    </div>
                    <div class="header-text">
                        <h1>ใบนัดตรวจโรคหัวใจและหลอดเลือด</h1>
                        <h2>โรงพยาบาลมหาราชนครราชสีมา</h2>
                    </div>
                </div>
                <div class="content">
                    <div class="left__content-container">
                        <div class="left__content-patient">
                            <p>เลขที่ใบส่งตัว <span>23839-1-64004261</span></p>
                            <p>เลขที่บัตรประชาชน <span>1 3020 00142 32 5</span></p>
                            <p>ชื่อ-สกุล <span>นางสาววังแก้ว บุญจันทึก</span></p>
                            <p>โทรศัพท์ <span>0933356365</span></p>
                            <p>สิทธิการรักษา <span>บัตรทองร่วมจ่าย 30 บาท</span></p>
                            <p>ผลการวินิจฉัย <span>Cardiac Arrhythmia</span></p>
                        </div>
                        <div class="left__content-before">
                            <p>การปฎิบัติก่อนมา</p>
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
                        </div>
                    </div>
                    <div class="right__content-container">
                        <div class="right__content-appoint">
                            <p>นัดพบ <span>นายแพทย์ กิตติพงศ์ ภิญโญสโมสร</span></p>
                            <p>วันนัด <span>วันจันทร์ที่ 30 สิงหาคม พ.ศ. 2564</span></p>
                            <p>เวลา <span>08.00 - 12.00 น.</span></p>
                            </div>
                        <div class="right__content-clinic">
                            <p>ยื่นใบนัดที่ <span>ห้องตรวจอายุรกรรม โซน ซี</span></p>
                            <p>อาคาร <span>ผู้ป่วยนอก</span></p>
                            <p>หมายเลขโทรศัพท์ <span>044232207</span></p>
                        </div>
                        <div class="right__content-remark">
                            <p>หมายเหตุ : <span>กรณีไม่สามารถมาตามนัดได้ หรือต้องการเลื่อนนัด ให้ติดต่อที่โรงพยาบาลที่ทำการออกใบนัด</span></p>
                        </div>
                    </div>
                    <div class="bottom-content">
                        <p>ขั้นตอนการรับบริการ</p>
                        <ul>
                            <li>1. ยื่นใบนัด / ใบส่งตัว (ออกจากระบบ R9Refer เท่านั้น) ที่ห้องตรวจอายุรกรรม โซน ซี</li>
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
                            <p>ผู้พิมพ์ใบนัด <span>นางสาววังแก้ว บุญจันทึก</span></p>
                            <p>วัน/เวลา ที่ลงนัด <span>25 สิงหาคม พ.ศ. 2564 : 10:59:03 น.</span></p>
                        </div>
                        <div class="right-footer">
                            <p>สถานพยาบาลออกใบส่งตัว</p>
                            <p><span>ศูนย์สุขภาพชุมชนเมือง 1 หัวทะเล</span></p>
                            <p>โทรศัพท์ <span>044395000 ต่อ 2510</span></p>
                        </div>
                    </div>
                </div>
            </div>
        ';

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output(APP_ROOT_DIR . '/public/downloads/test.pdf', 'F');
    }
}
