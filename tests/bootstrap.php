<?php use There4\Slim\Test\WebTestCase;

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('UTC');

require_once __DIR__.'/../vendor/autoload.php';

class LocalWebTestCase extends WebTestCase {
    public function getSlimInstance() {
        $capsule = new Illuminate\Database\Capsule\Manager;
        $capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'prefix'    => ''
        ]);
        $capsule->setEventDispatcher(new Illuminate\Events\Dispatcher());
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        require __DIR__.'/../app/schema.php';

        $app = new \Slim\Slim([
            'debug'          => false,
            'mode'           => 'testing',
            'templates.path' => __DIR__.'/../views'
        ]);
        $app->setName('default');
        require __DIR__.'/../app/app.php';
        return $app;
    }
};
