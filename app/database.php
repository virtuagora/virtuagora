<?php

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'virtuagora',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
]);
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher());
$capsule->setAsGlobal();
$capsule->bootEloquent();
date_default_timezone_set('America/Argentina/Buenos_Aires');
