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
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('email', new Augusthur\Validation\Rule\Email())
            ->addRule('password', new Augusthur\Validation\Rule\MaxLength(128));
        $req = $this->request;
        if ($validator->validate($req->post()) && $this->session->login($req->post('email'), $req->post('password'))) {
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
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('nombre', new Augusthur\Validation\Rule\NotEmpty())
            ->addRule('nombre', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Augusthur\Validation\Rule\MinLength(1))
            ->addRule('nombre', new Augusthur\Validation\Rule\MaxLength(32))
            ->addRule('apellido', new Augusthur\Validation\Rule\NotEmpty())
            ->addRule('apellido', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->addRule('apellido', new Augusthur\Validation\Rule\MinLength(1))
            ->addRule('apellido', new Augusthur\Validation\Rule\MaxLength(32))
            ->addRule('email', new Augusthur\Validation\Rule\NotEmpty())
            ->addRule('email', new Augusthur\Validation\Rule\Email())
            ->addRule('email', new Augusthur\Validation\Rule\Unique('usuarios'))
            ->addRule('password', new Augusthur\Validation\Rule\NotEmpty())
            ->addRule('password', new Augusthur\Validation\Rule\MinLength(8))
            ->addRule('password', new Augusthur\Validation\Rule\MaxLength(128))
            ->addRule('password', new Augusthur\Validation\Rule\Matches('password2'));
        $req = $this->request;
        if (!$validator->validate($req->post())) {
            throw (new TurnbackException())->setErrors($validator->getErrors());
        }
        $usuario = new Usuario;
        $usuario->email = $req->post('email');
        $usuario->password = password_hash($req->post('password'), PASSWORD_DEFAULT);
        $usuario->nombre = $req->post('nombre');
        $usuario->apellido = $req->post('apellido');
        $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->verificado = false;
        $usuario->puntos = 0;
        $usuario->suspendido = false;
        $usuario->es_funcionario = false;
        $usuario->es_jefe = false;
        $usuario->img_tipo = 1;
        $usuario->img_hash = md5(strtolower(trim($req->post('email'))));
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
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('id', new Augusthur\Validation\Rule\NumNatural())
            ->addRule('token', new Augusthur\Validation\Rule\MinLength(8))
            ->addRule('token', new Augusthur\Validation\Rule\AlphaNumeric());
        $data = array('id' => $id, 'token' => $token);
        if (!$validator->validate($data)) {
            $this->notFound();
        }
        $usuario = Usuario::findOrFail($id);
        if ($usuario->verificado) {
            $this->notFound();
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

}
