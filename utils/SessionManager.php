<?php

class SessionManager {

    public function login($email, $password) {
        $success = false;
        $usuario = Usuario::where('email', $email)->first();
        if (!is_null($usuario) && password_verify($password, $usuario->password)) {
            if ($usuario->verificado) {
                $success = true;
                $_SESSION['user'] = $usuario->toArray();
            }
        }
        return $success;
    }

    public function logout () {
        if (isset($_SESSION['user'])) {
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600);
            }
            session_destroy();
        }
    }

    public function user() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return null;
        }
    }

    public function exists() {
        return isset($_SESSION['user']);
    }

}
