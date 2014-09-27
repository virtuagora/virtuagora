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
    $app->render('registro-exito.twig', array('lala' => 'holis'));
});

$app->post('/registro', function () use ($app) {
    $req = $app->request;

    $usuario = new Usuario;
    $usuario->email = $req->post['email'];
    $usuario->password = $req->post['password'];
    $usuario->tiene_avatar = false;
    $usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
    $usuario->verificado = false;
    $usuario->save();

    $ciudadano = new Ciudadano;
    $ciudadano->id = $usuario->id;
    $ciudadano->nombre = $req->post['nombre'];
    $ciudadano->apellido = $req->post['apellido'];
    $ciudadano->descripcion = "";
    $ciudadano->prestigio = 0;
    $ciudadano->suspendido = false;
    $ciudadano->save();

    $to = $usuario->email;
    $subject = 'Confirma tu registro de Virtuagora';
    $message = 'Holis, te registraste en virtuagora. Entra a este link para confirmar tu email: ' .
        $app->request->getRootUri() . '/validar/' . $usuario->token_verificacion;
    $header = 'From:noreply@'.$_SERVER['SERVER_NAME'].' \r\n';
    $retval = mail($to, $subject, $message, $header);

    if($retval == true) {
        echo "Se manda el mail!";
    } else {
        echo "No se manda nada.";
    }
});

$app->get('/registro', function () use ($app) {
    $req = $app->request;

    require_once(__DIR__.'/../models/usuario.php');
    require_once(__DIR__.'/../models/ciudadano.php');

    $usuario = new Usuario;
    $usuario->email = "lalas@lele.com";
    $usuario->password = "hash_loco";
    $usuario->tiene_avatar = false;
    $usuario->token_verificacion = "token_loco";
    $usuario->verificado = false;
    $usuario->save();

    if ($usuario->id) {

    $ciudadano = new Ciudadano;
    $ciudadano->id = $usuario->id;
    $ciudadano->nombre = "Elbar";
    $ciudadano->apellido = "Budo";
    $ciudadano->descripcion = "Holis!";
    $ciudadano->prestigio = 0;
    $ciudadano->suspendido = false;
    $ciudadano->save();
    } else {
        var_dump($usuario->id);
    }
});

$app->run();
