<?php

class SessionManager {
    protected $mode;

    public function __construct($mode) {
        $this->mode = $mode;
    }

    public function login($email, $password) {
        $success = false;
        $usuario = Usuario::where('email', $email)->first();
        if ($usuario && password_verify($password, $usuario->password)) {
            if ($usuario->suspendido) {
                if (is_null($usuario->fin_suspension) || Carbon\Carbon::now()->lt($usuario->fin_suspension)) {
                    throw new TurnbackException('Su cuenta se encuentra suspendida.');
                } else {
                    $usuario->suspendido = false;
                    $usuario->fin_suspension = null;
                    $usuario->save();
                }
            }
            if ($usuario->advertencia && Carbon\Carbon::now()->gt($usuario->fin_advertencia)) {
                $usuario->advertencia = null;
                $usuario->fin_advertencia = null;
                $usuario->save();
            }
            $success = true;
            $this->update($usuario);
        }
        return $success;
    }

    public function logout() {
        if ($this->mode != 'testing' && $this->check()) {
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600);
            }
            session_destroy();
        } else {
            $_SESSION = array();
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
            $user = $this->getUser();
        }
        $_SESSION['user'] = $user->toArray();
        $_SESSION['user']['partido'] = $user->partido? $user->partido->toArray(): null;
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
                return $this->getUser()->es_funcionario;
            case 'mod':
                return !is_null($this->getUser()->patrulla_id);
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
        $mod = Usuario::whereHas('patrulla.poderes', function($q) use ($action) {
            $q->where('poder_id', $action);
        })->find($this->user('id'));
        return isset($mod);
    }

/* NO ES NECESARIO POR AHORA
    public function rolesAllowedTo($action) {
        return array('mod', 'fnc');
    }
*/

}
