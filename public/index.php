<?php require __DIR__.'/../vendor/autoload.php';

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
        $app->render('misc/fatal-error.twig', array('type' => get_class($e), 'exception' => $e));
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
$app->get('/usuario/:idUsr', function ($idUsr) use ($app) {
    if (strpos($app->request->headers->get('ACCEPT'), 'application/json') !== FALSE) {
        echo Usuario::findOrFail($idUsr)->toJson();
    }
});

$app->get('/test', function () use ($app) {
    var_dump(Contenido::findOrFail(1)->nombre ?: Contenido::findOrFail(1)->titulo);
    //var_dump($app->router()->getCurrentRoute());
    //$req = $app->request;
    //$p = new Paginator(Comentario::query(), $req->getUrl().$req->getPath(), $req->get(), 2, 1);
    //var_dump($p->links, $p->query->get()->toJson());
});

$app->get('/', 'PortalCtrl:verIndex')->name('shwIndex');
$app->get('/login', 'checkNoSession', 'PortalCtrl:verLogin')->name('shwLogin');
$app->post('/login', 'checkNoSession', 'PortalCtrl:login')->name('runLogin');
$app->post('/logout', 'PortalCtrl:logout')->name('runLogout');
$app->post('/registro', 'checkNoSession', 'PortalCtrl:registrar')->name('runCrearUsuario');
$app->get('/verificar/:idUsr/:token', 'PortalCtrl:verificarEmail')->name('runVerifUsuario');

$app->get('/perfil/cambiar-clave', checkRole('usr'), 'PortalCtrl:verCambiarClave')->name('shwModifClvUsuario');
$app->post('/perfil/cambiar-clave', checkRole('usr'), 'PortalCtrl:cambiarClave')->name('runModifClvUsuario');

$app->group('/admin', function () use ($app) {
    $app->get('/organismo', checkRole('mod'), 'AdminCtrl:verOrganismos')->name('shwAdmOrganis');
    $app->get('/organismo/crear', checkRole('mod'), 'AdminCtrl:verCrearOrganismo')->name('shwCrearOrganis');

    $app->get('/organismo/:idOrg/modificar', checkRole('mod'), 'AdminCtrl:verModificarOrganismo')->name('shwModifOrganis');
    $app->post('/organismo/:idOrg/modificar', checkRole('mod'), 'AdminCtrl:modificarOrganismo')->name('runModifOrganis');
    $app->post('/organismo/:idOrg/cambiar-imagen', checkRole('mod'), 'AdminCtrl:cambiarImgOrganismo')->name('runModifImgOrganis');
    $app->post('/organismo/:idOrg/eliminar', checkRole('mod'), 'AdminCtrl:eliminarOrganismo')->name('runElimiOrganis');

    $app->post('/organismo/crear', checkRole('mod'), 'AdminCtrl:crearOrganismo')->name('runCrearOrganis');
    $app->get('/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:verAdminFuncionarios')->name('shwAdmFuncion');
    $app->post('/organismo/:idOrg/funcionario', checkRole('mod'), 'AdminCtrl:adminFuncionarios')->name('runAdmFuncion');
    $app->post('/sancionar/:idUsr', checkRole('mod'), 'AdminCtrl:sancUsuario')->name('runSanUsuario');
});

$app->group('/propuesta', function () use ($app) {
    $app->get('/:idPro', 'PropuestaCtrl:ver')->name('shwPropues');
    $app->post('/:idPro/votar', checkRole('usr'), 'PropuestaCtrl:votar')->name('runVotarPropues');
    $app->post('/:idPro/cambiar-privacidad', checkRole('usr'), 'PropuestaCtrl:cambiarPrivacidad')->name('runModifPrvPropues');
    $app->post('/:idPro/eliminar', checkRole('usr'), 'PropuestaCtrl:eliminar')->name('runElimiPropues');
});

$app->group('/problematica', function () use ($app) {
    $app->get('/:idPro', 'ProblematicaCtrl:ver')->name('shwProblem');
    $app->post('/:idPro/votar', checkRole('usr'), 'ProblematicaCtrl:votar')->name('runVotarProblem');
});

$app->group('/documento', function () use ($app) {
    $app->get('/:idDoc', 'DocumentoCtrl:ver')->name('shwDocumen');
    $app->get('/:idDoc/v/:idVer', 'DocumentoCtrl:ver')->name('shwVerDocumen');
    $app->get('/:idDoc/modificar', 'DocumentoCtrl:verModificar')->name('shwModifDocumen');
    $app->post('/:idDoc/modificar', 'DocumentoCtrl:modificar')->name('runModifDocumen');
    $app->get('/:idDoc/nueva-version', 'DocumentoCtrl:verNuevaVersion')->name('shwNuVerDocumen');
    $app->post('/:idDoc/nueva-version', 'DocumentoCtrl:nuevaVersion')->name('runNuVerDocumen');
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
    $app->get('/documento', checkRole('fnc'), 'DocumentoCtrl:verCrear')->name('shwCrearDocumen');
    $app->post('/documento', checkRole('fnc'), 'DocumentoCtrl:crear')->name('runCrearDocumen');
    $app->get('/problematica', checkRole('usr'), 'ProblematicaCtrl:verCrear')->name('shwCrearProblem');
    $app->post('/problematica', checkRole('usr'), 'ProblematicaCtrl:crear')->name('runCrearProblem');
});

$app->post('/comentar/:tipoRaiz/:idRaiz', checkRole('usr'), 'ComentarioCtrl:comentar')->name('runComentar');

session_cache_limiter(false);
session_start();
$app->run();
