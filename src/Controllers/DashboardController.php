<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;

class DashboardController extends Controller
{
    public function getStatCard($req, $res, $args)
    {       
        $sql="SELECT count(id) as totalcase,
            count(case when (patient in (
                select id from appointment_online_db.patients where date(created_at) between '2021-08-01' and '2021-08-31')
            ) then id end) as newcase
            FROM appointment_online_db.appointments
            WHERE (appoint_date between '2021-08-01' and '2021-08-31') ";

        return $res->withJson(
            DB::select($sql, [$args['date']])
        );
    }

    public function getAppointPerDay($req, $res, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT CAST(DAY(appoint_date) AS SIGNED) AS d, COUNT(DISTINCT id) as amt
                FROM appointments WHERE (appoint_date between ? and ?)
                GROUP BY CAST(DAY(appoint_date) AS SIGNED) 
                ORDER BY CAST(DAY(appoint_date) AS SIGNED);";

        return $res->withJson(
            DB::select($sql, [$sdate, $edate])
        );
    }

    public function getAppointByClinic($req, $res, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT cl.clinic_name, (a.id) as amt
                FROM appointment_online_db.appointments a
                left join appointment_online_db.clinics cl on (a.clinic=cl.id)
                WHERE (a.appoint_date between ? and ?)
                group by a.clinic, cl.clinic_name
                order by a.clinic;";

        return $res->withJson(
            DB::select($sql, [$sdate, $edate])
        );
    }

    public function opVisitTypeDay($req, $res, $args)
    {        
        $sql="SELECT 
            CASE 
                WHEN (o.ovstist IN ('01', '03', '05', '06')) THEN 'Walkin'
                WHEN (o.ovstist='02') THEN 'Appoint'
                WHEN (o.ovstist='04') THEN 'Refer'
                WHEN (o.ovstist IN ('08', '09', '10')) THEN 'EMS'
                ELSE 'Unknown'
            END AS type,
            COUNT(DISTINCT vn) as num_pt
            FROM ovst o
            LEFT JOIN ovstist t ON (o.ovstist=t.ovstist)
            WHERE (vstdate=?)
            GROUP BY CASE 
                WHEN (o.ovstist IN ('01', '03', '05', '06')) THEN 'Walkin'
                WHEN (o.ovstist='02') THEN 'Appoint'
                WHEN (o.ovstist='04') THEN 'Refer'
                WHEN (o.ovstist IN ('08', '09', '10')) THEN 'EMS'
            END ";

        return $res->withJson(
            DB::select($sql, [$args['date']])
        );
    }

    public function ipClassYear($req, $res, $args)
    {
        $sdate = ($args['year'] - 1). '-10-01';
        $edate = $args['year']. '-09-30';
        
        $sql="SELECT 
            COUNT(CASE WHEN (ip.an IN (select an from ipt_icnp where (icnp_classification_id='1'))) THEN ip.an END) AS 'ประเภท 1',
            COUNT(CASE WHEN (ip.an IN (select an from ipt_icnp where (icnp_classification_id='2'))) THEN ip.an END) AS 'ประเภท 2',
            COUNT(CASE WHEN (ip.an IN (select an from ipt_icnp where (icnp_classification_id='3'))) THEN ip.an END) AS 'ประเภท 3',
            COUNT(CASE WHEN (ip.an IN (select an from ipt_icnp where (icnp_classification_id='4'))) THEN ip.an END) AS 'ประเภท 4',
            COUNT(CASE WHEN (ip.an IN (select an from ipt_icnp where (icnp_classification_id='5'))) THEN ip.an END) AS 'ประเภท 5',
            COUNT(CASE WHEN (ip.an not IN (select an from ipt_icnp)) THEN ip.an END) AS 'ไม่ระบุ'
            FROM ipt ip
            LEFT JOIN ward w ON (ip.ward=w.ward)
            WHERE (ip.dchdate BETWEEN ? AND ?) ";

        return $res->withJson(
            DB::select($sql, [$sdate, $edate])
        );
    }
}
