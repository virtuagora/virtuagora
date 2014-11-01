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

    public function user($attr = null) {
        if (isset($_SESSION['user'])) {
            if ($attr) {
                return $_SESSION['user'][$attr];
            } else {
                return $_SESSION['user'];
            }
        } else {
            return null;
        }
    }

    public function getUser() {
        if (isset($_SESSION['user'])) {
            return Usuario::find($_SESSION['user']['id']);
        } else {
            return null;
        }
    }

    public function exists() {
        return isset($_SESSION['user']);
    }

    public function hasRole($role) {
        if (!isset($_SESSION['user'])) return false;
        switch ($role) {
            case 'usr':
                return true;
            case 'fnc':
                return Usuario::where('id', $_SESSION['user']['id'])->pluck('es_funcionario');
            case 'mod':
                return !is_null(Moderador::find($_SESSION['user']['id']));
            default:
                return false;
        }
    }

}
