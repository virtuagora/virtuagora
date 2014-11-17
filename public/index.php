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
    $h = Usuario::find(1);
    $dt = Carbon\Carbon::now();
    $dt->subDays(3);
    var_dump($h->created_at, $dt, $dt->lt($h->updated_at));
});

$app->get('/', 'PortalCtrl:showIndex');
$app->get('/login', 'checkNoSession', 'PortalCtrl:showLogin');
$app->post('/login', 'checkNoSession', 'PortalCtrl:login');
$app->post('/logout', 'PortalCtrl:logout');
$app->post('/registro', 'checkNoSession', 'PortalCtrl:registrar');
$app->get('/validar/:idUsr/:token', 'PortalCtrl:validar');

$app->get('/admin/organismo', checkRole('mod'), 'AdminCtrl:showOrganismos');
$app->get('/admin/organismo/crear', checkRole('mod'), 'AdminCtrl:showCrearOrganismo');
$app->post('/admin/organismo/crear', checkRole('mod'), 'AdminCtrl:crearOrganismo');
$app->get('/admin/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:showAdminFuncionarios');
$app->post('/admin/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:adminFuncionarios');

$app->get('/propuesta/:idPro', 'PropuestaCtrl:showPropuesta');
$app->post('/propuesta/:idPro/votar', checkRole('usr'), 'PropuestaCtrl:votarPropuesta');
$app->get('/crear/propuesta', checkRole('fnc'), 'PropuestaCtrl:showCrearPropuesta');
$app->post('/crear/propuesta', checkRole('fnc'), 'PropuestaCtrl:crearPropuesta');

$app->get('/problematica/:idPro', 'ProblematicaCtrl:showProblematica');
$app->post('/problematica/:idPro/votar', checkRole('usr'), 'ProblematicaCtrl:votarProblematica');
$app->get('/crear/problematica', checkRole('usr'), 'ProblematicaCtrl:showCrearProblematica');
$app->post('/crear/problematica', checkRole('usr'), 'ProblematicaCtrl:crearProblematica');

$app->get('/partido', 'PartidoCtrl:showPartidos');
$app->post('/partido/:idPar/unirse', checkRole('usr'), 'PartidoCtrl:unirsePartido');
$app->post('/partido/dejar', checkRole('usr'), 'PartidoCtrl:dejarPartido');
$app->get('/crear/partido', checkRole('fnc'), 'PartidoCtrl:showCrearPartido');
$app->post('/crear/partido', checkRole('fnc'), 'PartidoCtrl:crearPartido');
$app->get('/modificar/partido/:idPar', checkRole('fnc'), 'PartidoCtrl:showModificarPartido');
$app->post('/modificar/partido/:idPar', checkRole('fnc'), 'PartidoCtrl:modificarPartido');

$app->post('/comentar/:tipoRaiz/:idRaiz', checkRole('usr'), 'ComentarioCtrl:comentar');

session_cache_limiter(false);
session_start();
$app->run();
