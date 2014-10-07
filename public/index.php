<?php
require __DIR__.'/../vendor/autoload.php';

// Prepare app
$app = new \Slim\Slim(array(
    'debug' => false,
    'templates.path' => '../views',
));

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    //'cache' => realpath('../views/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// Prepare singletons
$app->container->singleton('session', function () {
    return new SessionManager();
});

// Prepare error handler
$app->error(function (Exception $e) use ($app) {
    if ($e instanceof TurnbackException) {
        $app->flash('errors', $e->getErrors());
        $app->redirect($app->request->getReferrer());
    } else if ($e instanceof BearableException) {
        $app->render('error.twig', array('mensaje' => $e->getMessage()), $e->getCode());
    } else if ($e instanceof Illuminate\Database\Eloquent\ModelNotFoundException) {
        $app->notFound();
    } else {
        $mensaje = "Holis, hubo un error.";
        //$app->render('internal-error.twig', array('mensaje' => $mensaje));
    }
});

// Prepare hooks
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('baseUrl' => $app->request->getRootUri(),
                                   'username' => $app->session->username()));
});

// Prepare dispatcher
$app->get('/', function () use ($app) {
    $app->render('registro.twig', array('lala' => 'holis'));
});

$app->post('/registro', function () use ($app) {
    $validator = new Augusthur\Validation\Validator();
    $validator
        ->add_rule('nombre', new Augusthur\Validation\Rule\NotEmpty())
        ->add_rule('nombre', new Augusthur\Validation\Rule\Alpha())
        ->add_rule('nombre', new Augusthur\Validation\Rule\MinLength(1))
        ->add_rule('nombre', new Augusthur\Validation\Rule\MaxLength(32))
        ->add_rule('apellido', new Augusthur\Validation\Rule\NotEmpty())
        ->add_rule('apellido', new Augusthur\Validation\Rule\Alpha())
        ->add_rule('apellido', new Augusthur\Validation\Rule\MinLength(1))
        ->add_rule('apellido', new Augusthur\Validation\Rule\MaxLength(32))
        ->add_rule('email', new Augusthur\Validation\Rule\NotEmpty())
        ->add_rule('email', new Augusthur\Validation\Rule\Email())
        ->add_rule('email', new Augusthur\Validation\Rule\Unique('usuarios'))
        ->add_rule('password', new Augusthur\Validation\Rule\NotEmpty())
        ->add_rule('password', new Augusthur\Validation\Rule\MinLength(8))
        ->add_rule('password', new Augusthur\Validation\Rule\MaxLength(128))
        ->add_rule('password', new Augusthur\Validation\Rule\Matches('password2'));
    $req = $app->request;
    if (!$validator->is_valid($req->post())) {
        throw (new TurnbackException())->setErrors($validator->get_errors());
    }
    $usuario = new Usuario;
    $usuario->email = $req->post('email');
    $usuario->password = password_hash($req->post('password'), PASSWORD_DEFAULT);
    $usuario->nombre = $req->post('nombre');
    $usuario->apellido = $req->post('apellido');
    $usuario->tiene_avatar = false;
    $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
    $usuario->verificado = false;
    $usuario->save();
    $ciudadano = new Ciudadano;
    $ciudadano->id = $usuario->id;
    $ciudadano->descripcion = "";
    $ciudadano->prestigio = 0;
    $ciudadano->suspendido = false;
    $ciudadano->save();

    $to = $usuario->email;
    $subject = 'Confirma tu registro en Virtuagora';
    $message = 'Holis, te registraste en virtuagora. Entra a este link para confirmar tu email: ' .
               $req->getUrl() . $req->getRootUri() . '/validar/' .
               $usuario->id . '/' . $usuario->token_verificacion;
    $header = 'From:noreply@'.$_SERVER['SERVER_NAME'].' \r\n';
    $retval = mail($to, $subject, $message, $header);

    $app->render('registro-exito.twig', array('email' => $usuario->email));
});

$app->get('/validar/:usuario/:token', function ($id, $token) use ($app) {
    $validator = new Augusthur\Validation\Validator();
    $validator
        ->add_rule('id', new Augusthur\Validation\Rule\NumNatural())
        ->add_rule('token', new Augusthur\Validation\Rule\MinLength(8))
        ->add_rule('token', new Augusthur\Validation\Rule\AlphaNumeric());
    $data = array('id' => $id, 'token' => $token);
    if (!$validator->is_valid($data)) {
        $app->notFound();
    }
    $usuario = Usuario::findOrFail($id);
    if ($usuario->verificado) {
        $app->notFound();
    }
    if ($token == $usuario->token_verificacion) {
        $usuario->verificado = true;
        $usuario->save();
        $app->render('validar-correo.twig', array('usuarioValido' => true,
                                                  'email' => $usuario->email));
    } else {
        $app->render('validar-correo.twig', array('usuarioValido' => false));
    }
});

$app->post('/login', function () use ($app) {
    $validator = new Augusthur\Validation\Validator();
    $validator
        ->add_rule('email', new Augusthur\Validation\Rule\Email())
        ->add_rule('password', new Augusthur\Validation\Rule\MaxLength(128));
    $req = $app->request;
    if ($validator->is_valid($req->post()) && $app->session->login($req->post('email'), $req->post('password'))) {
        echo 'holis';
        $app->redirect($app->request->getReferrer());
    } else {
        echo 'chauchis';
        $app->flash('error', 'Datos de ingreso incorrectos. Por favor vuelva a intentarlo.');
        $app->redirect($app->request->getRootUri().'/login');
    }
});

$app->post('/logout', function () use ($app) {
    $app->session->logout();
    $app->redirect($app->request->getRootUri().'/');
});

session_cache_limiter(false);
session_start();
$app->run();
