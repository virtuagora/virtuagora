<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/database.php';
$app = new \Slim\Slim([
    'debug'          => false,
    'mode'           => 'production',
    'templates.path' => __DIR__.'/../views'
]);
require __DIR__.'/../app/app.php';
session_cache_limiter(false);
session_start();
$app->run();
