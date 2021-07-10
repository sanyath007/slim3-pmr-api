<?php

define('APP_ROOT_DIR', __DIR__ . '/../');

require APP_ROOT_DIR . 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT_DIR);
$dotenv->load();

$config = require APP_ROOT_DIR . 'config/app.php';

$app = new Slim\App($config);

require APP_ROOT_DIR . 'src/middleware.php';

require APP_ROOT_DIR . 'src/routes.php';

$app->run();
