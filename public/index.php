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
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension(), new ExtendedTwig());

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
        $app->render('misc/error.twig', array('mensaje' => $e->getMessage()), $e->getCode());
    } else if ($e instanceof Illuminate\Database\Eloquent\ModelNotFoundException) {
        $app->notFound();
    } else {
        $code = $e->getCode();
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = str_replace(array('#', '\n'), array('<div>#', '</div>'), $e->getTraceAsString());
        $html = '<h1>FATAL ERROR!</h1><p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>' . sprintf('<div><strong>Type:</strong> %s</div>', get_class($e));
        if ($code) $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        if ($message) $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        if ($file) $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        if ($line) $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }
        echo sprintf('<html><head><title>Virtuagora - Fail</title><style>body{margin:0;padding:30px;'.
                     'font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;'.
                     'font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style>'.
                     '</head><body>%s</body></html>', $html);
    }
});

// Prepare hooks
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('baseUrl' => $app->request->getRootUri(),
                                   'user' => $app->session->user()));
});

// Prepare middlewares
function checkNoSession() {
    global $app;
    if ($app->session->exists()) {
        $app->redirect($app->request->getRootUri());
    }
}

function checkRole($role) {
    return function () use ($role) {
        global $app;
        if (!$app->session->hasRole($role)) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
}

// Prepare dispatcher
$app->get('/usuario', function () use ($app) {
    if (strpos($app->request->headers->get('ACCEPT'), 'application/json') !== FALSE) {
        echo Usuario::all()->toJson();
    }
});

$app->get('/test', function () use ($app) {
    var_dump($app->urlFor('shwAdmOrganismos'));
});

$app->get('/', 'PortalCtrl:verIndex')->name('shwIndex');
$app->get('/login', 'checkNoSession', 'PortalCtrl:verLogin')->name('shwLogin');
$app->post('/login', 'checkNoSession', 'PortalCtrl:login')->name('runLogin');
$app->post('/logout', 'PortalCtrl:logout')->name('runLogout');
$app->post('/registro', 'checkNoSession', 'PortalCtrl:registrar')->name('runCrearUsuario');
$app->get('/validar/:idUsr/:token', 'PortalCtrl:validar')->name('runValidarUsuario');

$app->get('/perfil/cambiar-clave', checkRole('usr'), 'PortalCtrl:verCambiarClave')->name('shwModifClvUsuario');
$app->post('/perfil/cambiar-clave', checkRole('usr'), 'PortalCtrl:cambiarClave')->name('runModifClvUsuario');

$app->group('/admin', function () use ($app) {
    $app->get('/organismo', checkRole('mod'), 'AdminCtrl:verOrganismos')->name('shwAdmOrganis');
    $app->get('/organismo/crear', checkRole('mod'), 'AdminCtrl:verCrearOrganismo')->name('shwCrearOrganis');
    $app->post('/organismo/crear', checkRole('mod'), 'AdminCtrl:crearOrganismo')->name('runCrearOrganis');
    $app->get('/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:verAdminFuncionarios')->name('shwAdmFuncion');
    $app->post('/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:adminFuncionarios')->name('runAdmFuncion');
});

$app->group('/propuesta', function () use ($app) {
    $app->get('/:idPro', 'PropuestaCtrl:ver')->name('shwPropues');
    $app->post('/:idPro/votar', checkRole('usr'), 'PropuestaCtrl:votar')->name('runVotarPropues');
});

$app->group('/problematica', function () use ($app) {
    $app->get('/:idPro', 'ProblematicaCtrl:ver')->name('shwProblem');
    $app->post('/:idPro/votar', checkRole('usr'), 'ProblematicaCtrl:votar')->name('runVotarProblem');
});

$app->group('/partido', function () use ($app) {
    $app->get('', 'PartidoCtrl:listar')->name('shwListaPartido');
    $app->post('/:idPar/unirse', checkRole('usr'), 'PartidoCtrl:unirse')->name('runUnirsePartido');
    $app->post('/dejar', checkRole('usr'), 'PartidoCtrl:dejar')->name('runDejarPartido');
});
$app->get('/modificar/partido/:idPar', checkRole('fnc'), 'PartidoCtrl:verModificar')->name('shwModifPartido');
$app->post('/modificar/partido/:idPar', checkRole('fnc'), 'PartidoCtrl:modificar')->name('runModifPartido');
$app->post('/cambiar-imagen/partido/:idPar', checkRole('fnc'), 'PartidoCtrl:cambiarImagen')->name('runModifImgPartido');

$app->group('/crear', function () use ($app) {
    $app->get('/partido', checkRole('fnc'), 'PartidoCtrl:verCrear')->name('shwCrearPartido');
    $app->post('/partido', checkRole('fnc'), 'PartidoCtrl:crear')->name('runCrearPartido');
    $app->get('/propuesta', checkRole('fnc'), 'PropuestaCtrl:verCrear')->name('shwCrearPropues');
    $app->post('/propuesta', checkRole('fnc'), 'PropuestaCtrl:crear')->name('runCrearPropues');
    $app->get('/problematica', checkRole('usr'), 'ProblematicaCtrl:verCrear')->name('shwCrearProblem');
    $app->post('/problematica', checkRole('usr'), 'ProblematicaCtrl:crear')->name('runCrearProblem');
});

$app->post('/comentar/:tipoRaiz/:idRaiz', checkRole('usr'), 'ComentarioCtrl:comentar')->name('runComentar');

session_cache_limiter(false);
session_start();
$app->run();
