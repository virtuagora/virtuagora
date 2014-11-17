<?php use Augusthur\Validation as Validate;

class PortalCtrl extends Controller {

    public function showIndex() {
        if ($this->session->exists()) {
            $contenidos = Contenido::all();
            $this->render('usuario/portal.twig', array('contenidos' => $contenidos->toArray(),
                                                       'esModerador' => $this->session->hasRole('mod'),
                                                       'esFuncionario' => $this->session->hasRole('fnc')));
        } else {
            $this->render('registro/registro.twig');
        }
    }

    public function showLogin() {
        $this->render('login/login-static.twig');
    }

    public function login() {
        $vdt = new Validate\Validator();
        $vdt->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\MaxLength(128));
        $req = $this->request;
        if ($vdt->validate($req->post()) && $this->session->login($vdt->getData('email'), $vdt->getData('password'))) {
            $this->redirect($this->request->getReferrer());
        } else {
            $this->flash('error', 'Datos de ingreso incorrectos. Por favor vuelva a intentarlo.');
            $this->redirect($req->getRootUri().'/login');
        }
    }

    public function logout() {
        $this->session->logout();
        $this->redirect($this->request->getRootUri().'/');
    }

    public function registrar() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\NotEmpty())
            ->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(1))
            ->addRule('nombre', new Validate\Rule\MaxLength(32))
            ->addRule('apellido', new Validate\Rule\NotEmpty())
            ->addRule('apellido', new Validate\Rule\Alpha(array(' ')))
            ->addRule('apellido', new Validate\Rule\MinLength(1))
            ->addRule('apellido', new Validate\Rule\MaxLength(32))
            ->addRule('email', new Validate\Rule\NotEmpty())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('email', new Validate\Rule\Unique('usuarios'))
            ->addRule('password', new Validate\Rule\NotEmpty())
            ->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\Matches('password2'));
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = new Usuario;
        $usuario->email = $vdt->getData('email');
        $usuario->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $usuario->nombre = $vdt->getData('nombre');
        $usuario->apellido = $vdt->getData('apellido');
        $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->verificado = false;
        $usuario->puntos = 0;
        $usuario->suspendido = false;
        $usuario->es_funcionario = false;
        $usuario->es_jefe = false;
        $usuario->img_tipo = 1;
        $usuario->img_hash = md5(strtolower(trim($vdt->getData('email'))));
        $usuario->save();

        $to = $usuario->email;
        $subject = 'Confirma tu registro en Virtuagora';
        $message = 'Hola, te registraste en virtuagora. Entra a este link para confirmar tu email: ' .
                    $req->getUrl() . $req->getRootUri() . '/validar/' .
                    $usuario->id . '/' . $usuario->token_verificacion;
        $header = 'From:noreply@'.$_SERVER['SERVER_NAME'].' \r\n';
        $retval = mail($to, $subject, $message, $header);

        $this->render('registro/registro-exito.twig', array('email' => $usuario->email));
    }

    public function validar($id, $token) {
        $vdt = new Validate\Validator();
        $vdt->addRule('id', new Validate\Rule\NumNatural())
            ->addRule('token', new Validate\Rule\MinLength(8))
            ->addRule('token', new Validate\Rule\AlphaNumeric());
        $data = array('id' => $id, 'token' => $token);
        if (!$vdt->validate($data)) {
            $this->notFound();
        }
        $usuario = Usuario::findOrFail($id);
        if ($usuario->verificado) {
            $this->redirect($this->request->getRootUri().'/');
        }
        if ($token == $usuario->token_verificacion) {
            $usuario->verificado = true;
            $usuario->save();
            $this->render('registro/validar-correo.twig', array('usuarioValido' => true,
                                                                'email' => $usuario->email));
        } else {
            $this->render('registro/validar-correo.twig', array('usuarioValido' => false));
        }
    }

    public function showCambiarClave() {
        $this->render('perfil/cambiar-clave.twig');
    }

    public function cambiarClave() {
        $this->flash('success', 'Su contraseña fue modificada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

}
