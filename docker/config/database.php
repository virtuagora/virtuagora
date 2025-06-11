<?php

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: 'mysql',
    'database' => getenv('DB_NAME') ?: 'virtuagora',
    'username' => getenv('DB_USER') ?: 'virtuagora_user',
    'password' => getenv('DB_PASSWORD') ?: 'virtuagora_pass',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
]);
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher());
$capsule->setAsGlobal();
$capsule->bootEloquent();
date_default_timezone_set('America/Argentina/Buenos_Aires');