<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../../../vendor/autoload.php';

session_cache_limiter(false);
session_start();

$container = new \Slim\Container(require __DIR__ . '/../settings.php');
$app = new \Slim\App($container);

require __DIR__ . '/../dependencies.php';
require __DIR__ . '/../middleware.php';
require __DIR__ . '/../routes.php';

$app->run();
