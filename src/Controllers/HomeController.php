<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;

class HomeController extends Controller
{
    public function home($request, $response, $args)
    {
        return $response->withJson([
            'page' => 'Home page',
            'body' => 'This is Home page.'
        ]);
    }

    public function getStatTemp($request, $response, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT CAST(DAY(stat_date) AS SIGNED) AS d, temp
                FROM health_stats
                WHERE (stat_date BETWEEN ? AND ?)
                AND (patient_hn = ?)
                GROUP BY CAST(DAY(stat_date) AS SIGNED) 
                ORDER BY CAST(DAY(stat_date) AS SIGNED) ";

        return $response->withJson(
            DB::select($sql, [$sdate, $edate, $args['hn']])
        );
    }
    
    public function getStatO2($request, $response, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT CAST(DAY(stat_date) AS SIGNED) AS d, o2
                FROM health_stats
                WHERE (stat_date BETWEEN ? AND ?)
                AND (patient_hn = ?)
                GROUP BY CAST(DAY(stat_date) AS SIGNED) 
                ORDER BY CAST(DAY(stat_date) AS SIGNED) ";

        return $response->withJson(
            DB::select($sql, [$sdate, $edate, $args['hn']])
        );
    }
    public function getStatBp($request, $response, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT CAST(DAY(stat_date) AS SIGNED) AS d, bps, bpd
                FROM health_stats
                WHERE (stat_date BETWEEN ? AND ?)
                AND (patient_hn = ?)
                GROUP BY CAST(DAY(stat_date) AS SIGNED) 
                ORDER BY CAST(DAY(stat_date) AS SIGNED) ";

        return $response->withJson(
            DB::select($sql, [$sdate, $edate, $args['hn']])
        );
    }

    public function getStatPr($request, $response, $args)
    {
        $sdate = $args['month']. '-01';
        $edate = $args['month']. '-31';

        $sql="SELECT CAST(DAY(stat_date) AS SIGNED) AS d, pr
                FROM health_stats
                WHERE (stat_date BETWEEN ? AND ?)
                AND (patient_hn = ?)
                GROUP BY CAST(DAY(stat_date) AS SIGNED) 
                ORDER BY CAST(DAY(stat_date) AS SIGNED) ";

        return $response->withJson(
            DB::select($sql, [$sdate, $edate, $args['hn']])
        );
    }
}
