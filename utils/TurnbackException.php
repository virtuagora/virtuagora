<?php

class TurnbackException extends \RuntimeException {

    protected $errors = array();

    public function __construct($errors, $code = 200, Exception $previous = null) {
        if (!is_array($errors)) {
            $this->errors = array($errors);
        } else {
            $this->errors = $errors;
        }
        parent::__construct("Hubo errores en la última acción realizada.", $code, $previous);
    }

    public function setErrors($errors) {
		$this->errors = $errors;
		return $this;
	}

    public function getErrors() {
		return $this->errors;
	}

}
