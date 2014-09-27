<?php
require __DIR__.'/../vendor/autoload.php';

// Prepare app
$app = new \Slim\Slim(array(
    'debug' => true,
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

$app->get('/', function () use ($app) {
    $app->render('registro.twig', array('lala' => 'holis'));
});

$app->post('/registro', function () use ($app) {
    $req = $app->request;

    $usuario = new Usuario;
    $usuario->email = $req->post('email');
    $usuario->password = password_hash($req->post('password'), PASSWORD_DEFAULT);
    $usuario->tiene_avatar = false;
    $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
    $usuario->verificado = false;
    $usuario->save();

    $ciudadano = new Ciudadano;
    $ciudadano->id = $usuario->id;
    $ciudadano->nombre = $req->post('nombre');
    $ciudadano->apellido = $req->post('apellido');
    $ciudadano->descripcion = "";
    $ciudadano->prestigio = 0;
    $ciudadano->suspendido = false;
    $ciudadano->save();

    $to = $usuario->email;
    $subject = 'Confirma tu registro de Virtuagora';
    $message = 'Holis, te registraste en virtuagora. Entra a este link para confirmar tu email: ' .
        $req->getUrl() . '/validar/' . $usuario->token_verificacion;
    $header = 'From:noreply@'.$_SERVER['SERVER_NAME'].' \r\n';
    $retval = mail($to, $subject, $message, $header);

    if($retval == true) {
        echo "Se manda el mail!";
    } else {
        echo "No se manda nada.";
    }

    $app->render('registro-exito.twig', array('email' => $usuario->email));
});

$app->get('/validar/:usuario/:token', function ($usuario, $token) use ($app) {
    $req = $app->request;

    $usuario = Usuario::findOrFail(1);

    if ($codigo == $usuario->token_verificacion) {
        $usuario->verificado = true;
        $usuario->save();
    } else {
        echo 'pusiste cualquier codigo';
    }
});

$app->run();
