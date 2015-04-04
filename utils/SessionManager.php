<?php

class SessionManager {

    public function login($email, $password) {
        $success = false;
        $usuario = Usuario::where('email', $email)->first();
        if ($usuario && password_verify($password, $usuario->password) && $usuario->validado) {
            $success = true;
            $this->update($usuario);
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

    public function update($user = null) {
        if (is_null($user)) {
            $_SESSION['user'] = $this->getUser()->toArray();
        } else {
            $_SESSION['user'] = $user->toArray();
        }
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

    public function grantedRoles(array $roles) {
        $granted = array();
        foreach ($roles as $r) {
            if ($this->hasRole($r)) {
                $granted[] = $r;
            }
        }
        return $granted;
    }

    public function isAdminAllowedTo($action) {
        $mod = Moderador::whereHas('patrulla.poderes', function($q) {
            $q->where('accion', 'admConteni');
        })->find($this->user('id'));
        return isset($mod);
    }

/* NO ES NECESARIO POR AHORA
    public function rolesAllowedTo($action) {
        return array('mod', 'fnc');
    }
*/

}
