<?php

abstract class Controller {

    protected $app;

    public function __construct() {
        $this->app = \Slim\Slim::getInstance();
    }

    public function __get($name) {
        return $this->app->$name;
    }

    public function __set($name, $value) {
        return $this->app->$name = $value;
    }

    public function __call($name, $args) {
        return call_user_func_array(array($this->app, $name), $args);
    }

}
