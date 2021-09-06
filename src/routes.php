<?php

/** For request options http method */
$app->options('/{routes:.+}', function($request, $response, $args) {
    return $response;
});

/** =============== ROUTES =============== */
$app->get('/', 'HomeController:home')->setName('home');

$app->post('/login', 'LoginController:login')->setName('login');

$app->group('/api', function(Slim\App $app) {
    $app->get('/users', 'UserController:getAll');
    $app->get('/users/{username}', 'UserController:getUser');
    
    $app->get('/appointments', 'AppointmentController:getAll');
    $app->get('/appointments/{id}', 'AppointmentController:getById');
    $app->get('/appointments/{date}/count', 'AppointmentController:getCountByDate');
    $app->get('/appointments/init/form', 'AppointmentController:getInitForm');
    $app->post('/appointments', 'AppointmentController:store');
    $app->put('/appointments/{id}', 'AppointmentController:update');
    $app->delete('/appointments/{id}', 'AppointmentController:delete');

    $app->get('/admits', 'AdmitAppointmentController:getAll');
    $app->get('/admits/{id}', 'AdmitAppointmentController:getById');
    $app->get('/admits/{date}/count', 'AdmitAppointmentController:getCountByDate');
    $app->get('/admits/init/form', 'AdmitAppointmentController:getInitForm');
    $app->post('/admits', 'AdmitAppointmentController:store');
    $app->put('/admits/{id}', 'AdmitAppointmentController:update');
    $app->delete('/admits/{id}', 'AdmitAppointmentController:delete');

    $app->get('/patients', 'PatientController:getAll');
    $app->get('/patients/{id}', 'PatientController:getById');
    $app->get('/patients/{cid}/cid', 'PatientController:getByCid');
    $app->get('/hpatients/{cid}/cid', 'HPatientController:getByCid');

    $app->get('/departs', 'DepartmentController:getAll');
    $app->get('/departs/{id}', 'DepartmentController:getById');

    $app->get('/doctors', 'DoctorController:getAll');
    $app->get('/doctors/{id}', 'DoctorController:getById');
    $app->get('/doctors/init/form', 'DoctorController:getInitForm');
    $app->post('/doctors', 'DoctorController:store');
    $app->put('/doctors/{id}', 'DoctorController:update');
    $app->delete('/doctors/{id}', 'DoctorController:delete');
    $app->get('/doctors/{specialist}/clinic', 'DoctorController:getDortorsOfClinic');

    $app->get('/dashboard/{month}/stat-card', 'DashboardController:getStatCard');
    $app->get('/dashboard/{month}/appoint-day', 'DashboardController:getAppointPerDay');
    $app->get('/dashboard/{month}/appoint-by-clinic', 'DashboardController:getAppointByClinic');
});
/** =============== ROUTES =============== */

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
    /** using default slim page not found handler. */
    $handler = $this->notFoundHandler;

    return $handler($req, $res);
});
