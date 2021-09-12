<?php

use Tuupola\Middleware\HttpBasicAuthentication;

$container = $app->getContainer();

/** 
 * ============================================================
 * Inject error handler
 * ============================================================
 */
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $response
                ->withStatus(500)
                ->withHeader("Content-Type", "application/json")
                ->write($exception->getMessage());
    };
};

/** 
 * ============================================================
 * Inject data model with using Eloquent
 * ============================================================
 */
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($c) use ($capsule) {
    return $capsule;
};

/** 
 * ============================================================
 * Inject database connection 
 * ============================================================
 */
$container['pdo'] = function ($c) {
    try {
        $conStr = $c['settings']['homc_db'];

        $pdo = new PDO($conStr['driver']. ":Server=" .$conStr['host']. ";Database=" .$conStr['database'], $conStr['username'], $conStr['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
    catch(\Exception $ex) {
        return $ex->getMessage();
    }
};

/** 
 * ============================================================
 * Inject Auth class
 * ============================================================
 */
$container['auth'] = function($c) {
    return new App\Auth\Auth;
};

/** 
 * ============================================================
 * Inject Logger
 * ============================================================
 */
$container['logger'] = function($c) {
    $logger = new Monolog\Logger('My_logger');
    $file_handler = new Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);

    return $logger;
};

/** 
 * ============================================================
 * Inject JWT
 * ============================================================
 */
$container['jwt'] = function($c) {
    return new StdClass;
};

/** 
 * ============================================================
 * Inject Validator
 * ============================================================
 */
$container['validator'] = function($c) {
    return new App\Validation\validator;
};

/** 
 * ============================================================
 * Inject Controllers
 * ============================================================
 */
$container['LoginController'] = function($c) {
    return new App\Controllers\Auth\LoginController($c);
};

$container['HomeController'] = function($c) {
    return new App\Controllers\HomeController($c);
};

$container['UserController'] = function($c) {
    return new App\Controllers\UserController($c);
};

$container['DoctorController'] = function($c) {
    return new App\Controllers\DoctorController($c);
};

$container['AppointmentController'] = function($c) {
    return new App\Controllers\AppointmentController($c);
};

$container['PatientController'] = function($c) {
    return new App\Controllers\PatientController($c);
};

$container['HPatientController'] = function($c) {
    return new App\Controllers\HPatientController($c);
};

$container['DepartmentController'] = function($c) {
    return new App\Controllers\DepartmentController($c);
};

$container['DashboardController'] = function($c) {
    return new App\Controllers\DashboardController($c);
};

/** 
 * ============================================================
 * JWT middleware
 * ============================================================
 */
$app->add(new Slim\Middleware\JwtAuthentication([
    "path"          => '/api',
    "logger"        => $container['logger'],
    "secure"        => false,
    "passthrough"   => ["/test"],
    "secret"        => getenv("JWT_SECRET"),
    "callback"      => function($req, $res, $args) use ($container) {
        $container['jwt'] = $args['decoded'];
    },
    "error"         => function($req, $res, $args) {
        $data["status"] = "0";
        $data["message"] = $args["message"];
        $data["data"] = "";
        
        return $res
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

/** 
 * ============================================================
 * CORS middleware
 * ============================================================
 */
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);

    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});
