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

    $app->get('/patients', 'PatientController:getAll');
    $app->get('/patients/{hn}', 'PatientController:getById');
});
/** =============== ROUTES =============== */

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
    /** using default slim page not found handler. */
    $handler = $this->notFoundHandler;

    return $handler($req, $res);
});
