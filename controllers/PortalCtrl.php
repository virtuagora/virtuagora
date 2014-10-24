<?php

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
            ->add_rule('email', new Augusthur\Validation\Rule\Email())
            ->add_rule('password', new Augusthur\Validation\Rule\MaxLength(128));
        $req = $this->request;
        if ($validator->is_valid($req->post()) && $this->session->login($req->post('email'), $req->post('password'))) {
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
            ->add_rule('nombre', new Augusthur\Validation\Rule\NotEmpty())
            ->add_rule('nombre', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->add_rule('nombre', new Augusthur\Validation\Rule\MinLength(1))
            ->add_rule('nombre', new Augusthur\Validation\Rule\MaxLength(32))
            ->add_rule('apellido', new Augusthur\Validation\Rule\NotEmpty())
            ->add_rule('apellido', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->add_rule('apellido', new Augusthur\Validation\Rule\MinLength(1))
            ->add_rule('apellido', new Augusthur\Validation\Rule\MaxLength(32))
            ->add_rule('email', new Augusthur\Validation\Rule\NotEmpty())
            ->add_rule('email', new Augusthur\Validation\Rule\Email())
            ->add_rule('email', new Augusthur\Validation\Rule\Unique('usuarios'))
            ->add_rule('password', new Augusthur\Validation\Rule\NotEmpty())
            ->add_rule('password', new Augusthur\Validation\Rule\MinLength(8))
            ->add_rule('password', new Augusthur\Validation\Rule\MaxLength(128))
            ->add_rule('password', new Augusthur\Validation\Rule\Matches('password2'));
        $req = $this->request;
        if (!$validator->is_valid($req->post())) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
        }
        $usuario = new Usuario;
        $usuario->email = $req->post('email');
        $usuario->password = password_hash($req->post('password'), PASSWORD_DEFAULT);
        $usuario->nombre = $req->post('nombre');
        $usuario->apellido = $req->post('apellido');
        $usuario->imagen = false;
        $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->verificado = false;
        $usuario->puntos = 0;
        $usuario->suspendido = false;
        $usuario->es_funcionario = false;
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
            ->add_rule('id', new Augusthur\Validation\Rule\NumNatural())
            ->add_rule('token', new Augusthur\Validation\Rule\MinLength(8))
            ->add_rule('token', new Augusthur\Validation\Rule\AlphaNumeric());
        $data = array('id' => $id, 'token' => $token);
        if (!$validator->is_valid($data)) {
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
