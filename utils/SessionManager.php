<?php

class SessionManager {

    public function login($email, $password) {
        $success = false;
        $usuario = Usuario::where('email', $email)->first();
        if (!is_null($usuario) && password_verify($password, $usuario->password)) {
            if ($usuario->verificado) {
                $success = true;
                $this->setUser($usuario);
            }
        }
        return $success;
    }

    public function logout () {
        if ($this->check()) {
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600);
            }
            session_destroy();
        }
    }

    public function check($idUsr = null) {
        if ($idUsr) {
            return $this->user('id') == $idUsr;
        } else {
            return isset($_SESSION['user']);
        }
    }

    public function user($attr = null) {
        if ($this->check()) {
            if ($attr) {
                return $_SESSION['user'][$attr];
            } else {
                return $_SESSION['user'];
            }
        } else {
            return null;
        }
    }

    public function setUser($user = null) {
        $_SESSION['user'] = $user->toArray();
        $_SESSION['user']['es_moderador'] = $this->hasRole('mod');
    }

    public function getUser() {
        if ($this->check()) {
            return Usuario::find($this->user('id'));
        } else {
            return null;
        }
    }

    public function hasRole($role) {
        if (!$this->check()) return false;
        switch ($role) {
            case 'usr':
                return true;
            case 'fnc':
                return Usuario::where('id', $this->user('id'))->pluck('es_funcionario');
            case 'mod':
                return !is_null(Moderador::find($this->user('id')));
            default:
                return false;
        }
    }

}
