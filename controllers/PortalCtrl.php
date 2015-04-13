<?php use Augusthur\Validation as Validate;

class PortalCtrl extends Controller {

    public function verIndex() {
        if ($this->session->check()) {
            $contenidos = Contenido::all();
            $notificaciones = Notificacion::all();
            $this->render('usuario/portal.twig', array('contenidos' => $contenidos->toArray(), 'notificaciones' => $notificaciones->toArray()));
        } else {
            $this->render('introduccion.twig');
            //echo 'holis';
        }
    }

    public function verLogin() {
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
            $this->flash('errors', array('Datos de ingreso incorrectos. Por favor vuelva a intentarlo.'));
            $this->redirectTo('shwLogin');
        }
    }

    public function logout() {
        $this->session->logout();
        $this->redirectTo('shwIndex');
    }

    public function verRegistrar() {
        $this->render('registro/registro.twig');
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
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = new Usuario;
        $usuario->email = $vdt->getData('email');
        $usuario->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $usuario->nombre = $vdt->getData('nombre');
        $usuario->apellido = $vdt->getData('apellido');
        $usuario->emailed_token = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->validado = false;
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
                   $this->urlFor('runValidUsuario', array('idUsr' => $usuario->id, 'token' => $usuario->emailed_token));
        $header = 'From:noreply@'.$_SERVER['SERVER_NAME'].' \r\n';
        mail($to, $subject, $message, $header);

        $this->render('registro/registro-exito.twig', array('email' => $usuario->email));
    }

    public function verificarEmail($idUsr, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idUsr, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\MinLength(8));
        $usuario = Usuario::findOrFail($idUsr);
        if ($usuario->validado) {
            $this->flash('warning', 'Su cuenta ya cuenta con un email validado.');
            $this->redirectTo('shwIndex');
        }
        if ($token == $usuario->emailed_token) {
            $usuario->validado = true;
            $usuario->save();
            $this->render('registro/validar-correo.twig', array('usuarioValido' => true,
                                                                'email' => $usuario->email));
        } else {
            $this->render('registro/validar-correo.twig', array('usuarioValido' => false));
        }
    }

}
