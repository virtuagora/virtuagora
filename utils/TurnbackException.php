<?php

class TurnbackException extends \RuntimeException {

    protected $errors = array();

    public function setErrors($errors) {
		$this->errors = $errors;
		$this->message = "Hubo errores en la última acción realizada.";
		return $this;
	}

    public function getErrors() {
		return $this->errors;
	}

}
