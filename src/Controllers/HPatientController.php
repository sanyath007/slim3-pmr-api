<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;

class HPatientController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');

        $patients = Patient::orderBy('hn')->get();

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
        // $sql = "SELECT top (1) bh.useDrg,ph.pay_typedes,
		// 	CAST(CAST(substring(bh.rigthDate, 1, 4) AS int) - 543 AS varchar(4)) + '-' + substring(bh.rigthDate, 5, 2) + '-' + substring(bh.rigthDate, 7, 2) AS rigthDateNew,
		// 	ltrim(rtrim(pt.hn)) AS hn,
		// 	case when len(ps.CardID)>13 then ( substring(ps.CardID,1,1)+substring(ps.CardID,3,4)+substring(ps.CardID,8,5)+substring(ps.CardID,14,2)+substring(ps.CardID,17,1)) else ps.CardID end AS CardID,
		// 	rtrim(ltrim(c.titleName))+rtrim(ltrim(pt.firstName))+'  '+rtrim(ltrim(pt.lastName)) AS ptName, pt.sex,
		// 	CAST(CAST(substring(pt.birthDay, 1, 4) AS int) - 543 AS varchar(4)) + '-' + substring(pt.birthDay, 5, 2) + '-' + substring(pt.birthDay, 7, 2) AS birthDay,
		// 	substring(CAST(CAST(CAST(CONVERT(varchar(8), GETDATE(), 112) AS int) + 5430000 AS int) AS char(8)), 1, 4) - CAST(substring(pt.birthDay, 1, 4) AS int) AS Age,
		// 	ltrim(rtrim(pt.addr1)) AS addrNum,
		// 	ltrim(rtrim(pt.moo)) AS addrMoo,
		// 	ltrim(rtrim(pt.addr2)) AS addrTown,
		// 	ltrim(rtrim(n.tambonName)) AS tumbon,
		// 	ltrim(rtrim(m.regionName)) AS aumper,
		// 	ltrim(rtrim(a.areaName)) AS province,pt.phone
		// 	FROM  PATIENT pt with(nolock) 
		// 	left join PTITLE c with (nolock) on (pt.titleCode=c.titleCode)
		// 	left join PatSS ps with (nolock) on (pt.hn=ps.hn)
		// 	left join Bill_h bh with (nolock) on (pt.hn=bh.hn)
		// 	left join Paytype ph with (nolock) on (bh.useDrg=ph.pay_typecode)
		// 	left join AREA a with(nolock) on (pt.areaCode=a.areaCode)
		// 	left join REGION m with(nolock) on (pt.regionCode =m.regionCode)  
		// 	left join Tambon n with(nolock) on (n.tambonCode=pt.regionCode+pt.tambonCode)
		// 	WHERE $strSearch_strlen
		// 	order by rigthDateNew desc";

        // $homc_result=odbc_exec($connHOMC,$sql);
        // $recHOMC=odbc_fetch_array($homc_result);

        // $useDrg = $recHOMC['useDrg'] ;
        // $pay_typedes = $recHOMC['pay_typedes'] ;
        // $phone = $recHOMC['phone'] ;
        // $addr_num = $recHOMC['addrNum'] ;
        // $addr_moo = $recHOMC['addrMoo'] ;
        // $addr_town = $recHOMC['addrTown'] ;
        // $tumbon = $recHOMC['tumbon'] ;
        // $aumper = $recHOMC['aumper'] ;
        // $province = $recHOMC['province'] ;
        // $birthDay = $recHOMC['birthDay'] ;
        // $sex = $recHOMC['sex'] ;
        // $arrYear=split("-",$recHOMC['birthDay']);
        // for($ii=1; $ii<=count($arrYear); $ii++){
        //     $M[$ii] = $arrYear[$ii-1];
        // }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        try {
            $cid = (int)$args['cid'];
            $sql = "SELECT top (1) bh.useDrg,ph.pay_typedes,
			CAST(CAST(substring(bh.rigthDate, 1, 4) AS int) - 543 AS varchar(4)) + '-' + substring(bh.rigthDate, 5, 2) + '-' + substring(bh.rigthDate, 7, 2) AS rigthDateNew,
			ltrim(rtrim(pt.hn)) AS hn,
			case when len(ps.CardID)>13 then (substring(ps.CardID,1,1)+substring(ps.CardID,3,4)+substring(ps.CardID,8,5)+substring(ps.CardID,14,2)+substring(ps.CardID,17,1)) else ps.CardID end AS cid,
			rtrim(ltrim(c.titleName)) as pname, rtrim(ltrim(pt.firstName)) as firstName, rtrim(ltrim(pt.lastName)) AS lastName, pt.sex, pt.phone,
			CAST(CAST(substring(pt.birthDay, 1, 4) AS int) - 543 AS varchar(4)) + '-' + substring(pt.birthDay, 5, 2) + '-' + substring(pt.birthDay, 7, 2) AS birthDay,
			substring(CAST(CAST(CAST(CONVERT(varchar(8), GETDATE(), 112) AS int) + 5430000 AS int) AS char(8)), 1, 4) - CAST(substring(pt.birthDay, 1, 4) AS int) AS Age,
			ltrim(rtrim(pt.addr1)) AS addrNum,
			ltrim(rtrim(pt.moo)) AS addrMoo,
			ltrim(rtrim(pt.addr2)) AS addrTown,
			ltrim(rtrim(n.tambonName)) AS tumbon,
			ltrim(rtrim(m.regionName)) AS aumper,
			ltrim(rtrim(a.areaName)) AS province,pt.phone
			FROM  PATIENT pt with(nolock) 
			left join PTITLE c with (nolock) on (pt.titleCode=c.titleCode)
			left join PatSS ps with (nolock) on (pt.hn=ps.hn)
			left join Bill_h bh with (nolock) on (pt.hn=bh.hn)
			left join Paytype ph with (nolock) on (bh.useDrg=ph.pay_typecode)
			left join AREA a with(nolock) on (pt.areaCode=a.areaCode)
			left join REGION m with(nolock) on (pt.regionCode =m.regionCode)  
			left join Tambon n with(nolock) on (n.tambonCode=pt.regionCode+pt.tambonCode)
			WHERE (pt.hn=:hn)
			order by rigthDateNew desc";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':hn', $cid, \PDO::PARAM_INT);
            $stmt->execute();

            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = json_encode($patient, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write($data);
        } catch(\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    // public function getFormInit($request, $response, $args)
    // {                    
    //     $data = json_encode([
    //         'blood_groups'  => BloodGroup::all(),
    //         'rights'        => Right::all(),
    //         'tambons'       => Tambon::all(),
    //         'amphurs'       => Amphur::all(),
    //         'changwats'     => Changwat::all(),
    //         'nationalities' => Nationality::orderBy('nhso_code')->get()
    //     ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

    //     return $response->withStatus(200)
    //             ->withHeader("Content-Type", "application/json")
    //             ->write($data);
    // }

    // public function store($request, $response, $args)
    // {
    //     $this->validator->validate($request, [
    //         'hn'            => v::numeric()->length(7, 7),
    //         'cid'           => v::numeric()->length(13, 13),
    //         'pname'         => v::stringType()->notEmpty(),
    //         'fname'         => v::stringType()->notEmpty(),
    //         'lname'         => v::stringType()->notEmpty(),
    //         'sex'           => v::numeric()->notEmpty(),
    //         'birthdate'     => v::date()->notEmpty(),
    //         'tel1'          => v::stringType()->notEmpty(),
    //         'address'       => v::stringType()->notEmpty(),
    //         'tambon'        => v::stringType()->notEmpty(),
    //         'amphur'        => v::stringType()->notEmpty(),
    //         'changwat'      => v::stringType()->notEmpty(),
    //         'zipcode'       => v::numeric()->length(5, 5),
    //         'nationality'   => v::stringType()->notEmpty(),
    //         'reg_date'      => v::date()->notEmpty(),
    //         'line_id'       => v::stringType()->notEmpty(),
    //         'gmap_url'      => v::stringType()->notEmpty(),
    //     ]);

    //     if ($this->validator->failed()) {
    //         return $response
    //                 ->withStatus(200)
    //                 ->withHeader("Content-Type", "application/json")
    //                 ->write(json_encode([
    //                     'status' => 0,
    //                     'message' => 'Data Invalid !!',
    //                     'errors' => $this->validator->getMessages()
    //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //     }

    //     try {
    //         $post = (array)$request->getParsedBody();

    //         $patient = new Patient;
    //         $patient->id            = Uuid::uuid4();
    //         $patient->hn            = $post['hn'];
    //         $patient->cid           = $post['cid'];
    //         $patient->pname         = $post['pname'];
    //         $patient->fname         = $post['fname'];
    //         $patient->lname         = $post['lname'];
    //         $patient->sex           = $post['sex'];
    //         $patient->birthdate     = $post['birthdate'];
    //         $patient->address       = $post['address'];
    //         $patient->moo           = $post['moo'];
    //         $patient->road          = $post['road'];
    //         $patient->tambon        = $post['tambon'];
    //         $patient->amphur        = $post['amphur'];
    //         $patient->changwat      = $post['changwat'];
    //         $patient->zipcode       = $post['zipcode'];
    //         $patient->tel1          = $post['tel1'];
    //         $patient->tel2          = $post['tel2'];
    //         $patient->main_right    = $post['right'];
    //         $patient->hosp_main     = $post['hosp_main'];
    //         $patient->passport      = $post['passport'];
    //         $patient->nationality   = $post['nationality'];
    //         $patient->race          = $post['race'];
    //         $patient->blood_group   = $post['blood_group'];
    //         $patient->reg_date      = $post['reg_date'];
    //         $patient->line_id       = $post['line_id'];
    //         $patient->gmap_url      = $post['gmap_url'];
    //         $patient->verify_hashed = password_hash($post['tel1'], PASSWORD_BCRYPT);

    //         /** Upload image */
    //         $upload_url = 'http://'.$request->getServerParam('SERVER_NAME').$request->getServerParam('PHP_SELF');
    //         $img_url = uploadImage($post['img_url'], $upload_url);
    //         $patient->img_url = $img_url;

    //         if($patient->save()) {
    //             if (count($post['visits']) > 0) {
    //                 foreach ($post['visits'] as $vs) {
    //                     $visit              = new Visit();
    //                     $visit->vn          = Uuid::uuid4()->toString();
    //                     $visit->an          = $vs['an'];
    //                     $visit->visit_date  = $vs['visit_date'];
    //                     $visit->visit_time  = $vs['visit_time'];
    //                     $visit->detail      = $vs['detail'];
    //                     $visit->patient_hn  = $patient->hn;
    //                     $visit->visit_right = $patient->right;
    //                     $visit->is_admit    = 1;

    //                     $visit->save();
    //                 }
    //             }

    //             return $response
    //                     ->withStatus(200)
    //                     ->withHeader("Content-Type", "application/json")
    //                     ->write(json_encode([
    //                         'status' => 1,
    //                         'message' => 'Inserting successfully',
    //                         'patient' => $patient
    //                     ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //         } else {
    //             return $response
    //                 ->withStatus(500)
    //                 ->withHeader("Content-Type", "application/json")
    //                 ->write(json_encode([
    //                     'status' => 0,
    //                     'message' => 'Something went wrong!!'
    //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //         }
    //     } catch (\Exception $ex) {
    //         return $response
    //                 ->withStatus(500)
    //                 ->withHeader("Content-Type", "application/json")
    //                 ->write(json_encode([
    //                     'status' => 0,
    //                     'message' => $ex->getMessage()
    //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //     }
    // }

    // public function update($request, $response, $args)
    // {
    //     try {
    //         $post = (array)$request->getParsedBody();

    //         $patient = Patient::where('hn', $post['hn'])->first();
    //         $patient->hn = $post['hn'];
    //         $patient->cid = $post['cid'];
    //         $patient->pname = $post['pname'];
    //         $patient->fname = $post['fname'];
    //         $patient->lname = $post['lname'];
    //         $patient->sex = $post['sex'];
    //         $patient->birthdate = $post['birthdate'];
    //         $patient->address = $post['address'];
    //         $patient->moo = $post['moo'];
    //         $patient->road = $post['road'];
    //         $patient->tambon = $post['tambon'];
    //         $patient->amphur = $post['amphur'];
    //         $patient->changwat = $post['changwat'];
    //         $patient->zipcode = $post['zipcode'];
    //         $patient->tel1 = $post['tel1'];
    //         $patient->tel2 = $post['tel2'];
    //         $patient->right = $post['right'];
    //         // $patient->hosp_main = $post['hosp_main'];
    //         // $patient->passport = $post['passport'];
    //         $patient->nationality = $post['nationality'];
    //         // $patient->race = $post['race'];
    //         $patient->blood_group = $post['blood_group'];
    //         $patient->reg_date = $post['reg_date'];
    //         $patient->line_id = $post['line_id'];
    //         $patient->gmap_url = $post['gmap_url'];
    //         $patient->verify_hashed = password_hash($post['tel1'], PASSWORD_BCRYPT);

    //         /** Upload image */
    //         $upload_url = 'http://'.$request->getServerParam('SERVER_NAME').$request->getServerParam('PHP_SELF');
    //         $img_url = uploadImage($post['img_url'], $upload_url);
    //         $patient->img_url = $img_url;

    //         if($patient->save()) {
    //             return $response
    //                     ->withStatus(200)
    //                     ->withHeader("Content-Type", "application/json")
    //                     ->write(json_encode([
    //                         'status' => 1,
    //                         'message' => 'Inserting successfully',
    //                         'patient' => $patient
    //                     ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //         } else {
    //             return $response
    //                 ->withStatus(500)
    //                 ->withHeader("Content-Type", "application/json")
    //                 ->write(json_encode([
    //                     'status' => 0,
    //                     'message' => 'Something went wrong!!'
    //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //         }
    //     } catch (\Exception $ex) {
    //         return $response
    //                 ->withStatus(500)
    //                 ->withHeader("Content-Type", "application/json")
    //                 ->write(json_encode([
    //                     'status' => 0,
    //                     'message' => $ex->getMessage()
    //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    //     }
    // }
}
