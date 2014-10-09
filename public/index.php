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
        $code = $e->getCode();
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = str_replace(array('#', '\n'), array('<div>#', '</div>'), $e->getTraceAsString());
        $html = '<h1>FATAL ERROR!</h1>';
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($e));
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }
        echo sprintf("<html><head><title>Virtuagora - Fail</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>", $html);
    }
});

// Prepare hooks
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('baseUrl' => $app->request->getRootUri(),
                                   'username' => $app->session->username()));
});

$app->get('/usuario', function () use ($app) {
    if (strpos($app->request->headers->get('ACCEPT'), 'application/json') !== FALSE) {
        echo Usuario::all()->toJson();
    }
});

// Prepare dispatcher
$app->get('/', function () use ($app) {
    if ($app->session->exists()) {
        $app->render('portal.twig');
    } else {
        $app->render('registro.twig', array('lala' => 'holis'));
    }
});

$app->post('/registro', function () use ($app) {
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
    $req = $app->request;
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
    $usuario->prestigio = 0;
    $usuario->suspendido = false;
    $usuario->es_funcionario = false;
    $usuario->save();

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

$app->get('/login', function () use ($app) {
    $app->render('login-static.twig');
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

$app->get('/admin/organismos', function () use ($app) {
    $organismos = Organismo::all();
    echo Organismo::all()->toJson();
    echo Organismo::first()->usuarios->toJson();
    //$app->render('login-static.twig');
});

session_cache_limiter(false);
session_start();
$app->run();
