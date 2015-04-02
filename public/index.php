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

$app->api = false;

// Prepare error handler
$app->error(function (Exception $e) use ($app) {
    if ($app->api) {
        $msg = array('code' => $e->getCode(), 'message' => $e->getMessage());
        if ($e instanceof TurnbackException) {
            $msg['errors'] = $e->getErrors();
        }
        echo json_encode($msg);
    } else {
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
    }
});

// Prepare hooks
$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('user' => $app->session->user()));
});

// Prepare middlewares
function checkNoSession() {
    global $app;
    if ($app->session->check()) {
        $app->redirect($app->request->getRootUri());
    }
}

function checkRole($role) {
    return function () use ($role) {
        global $app;
        if (is_array($role)) {
            $rejected = empty($app->session->grantedRoles($role));
        } else {
            $rejected = !$app->session->hasRole($role);
        }
        if ($rejected) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
}

function checkUserAuth($action, $checkMod = false) {
    global $app;
    $roles = $app->session->rolesAllowedTo($action);
    if ($checkMod && count($roles) == 1 && $roles[0] == 'mod') {
        return checkAdminAuth('admConteni');
    } else {
        return checkRole($roles);
    }
}

function checkAdminAuth($action) {
    return function () use ($action) {
        global $app;
        if (!$app->session->isAdminAllowedTo($action)) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
}

/*
checkAdminAuth('accion')

checkUserAuth('accion') <------------- por ahora uso checkRole

checkAdminOrUserAuth('accion')

---

rolesAllowedTo('accion') -> ['rol1', 'rol2', ...]

grantedRoles(['rolX', 'rolY', ...]) -> ['rol1']
*/

$app->get('/test', function () use ($app) {
    /*$mod = Moderador::with(['patrulla.poderes' => function($query) {
        $query->where('accion', 'admContenis');
    }])->find(1);*/

    $mod = Moderador::whereHas('patrulla.poderes', function($q) {
        $q->where('accion', 'admConteni');
    })->find(1);

    var_dump($mod);
    //$c->load('contenidos');
    //var_dump($c->contenidos()->toArray());
    //var_dump(Contenido::findOrFail(1)->nombre ?: Contenido::findOrFail(1)->titulo);
    //var_dump($app->router()->getCurrentRoute());
    //$req = $app->request;
    //$p = new Paginator(Comentario::query(), $req->getUrl().$req->getPath(), $req->get(), 2, 1);
    //var_dump($p->links, $p->query->get()->toJson());
});

$app->get('/', 'PortalCtrl:verIndex')->name('shwIndex');
$app->get('/login', 'checkNoSession', 'PortalCtrl:verLogin')->name('shwLogin');
$app->post('/login', 'checkNoSession', 'PortalCtrl:login')->name('runLogin');
$app->post('/logout', 'PortalCtrl:logout')->name('runLogout');
$app->get('/registro', 'checkNoSession', 'PortalCtrl:verRegistrar')->name('shwCrearUsuario');
$app->post('/registro', 'checkNoSession', 'PortalCtrl:registrar')->name('runCrearUsuario');
$app->get('/verificar/:idUsr/:token', 'PortalCtrl:verificarEmail')->name('runVerifUsuario');

$app->get('/contenido/:idCon', 'ContenidoCtrl:ver')->name('shwConteni');
$app->get('/contenido', 'ContenidoCtrl:listar')->name('shwListaConteni');

$app->get('/usuario/:idUsr', 'UsuarioCtrl:ver')->name('shwUsuario');
$app->get('/usuario', 'UsuarioCtrl:listar')->name('shwListaUsuario');

$app->post('/comentar/:tipoRaiz/:idRaiz', checkRole('usr'), 'ComentarioCtrl:comentar')->name('runComentar');

$app->group('/perfil', function () use ($app) {
    $app->get('/modificar', checkRole('usr'), 'UsuarioCtrl:verModificar')->name('shwModifUsuario');
    $app->post('/modificar', checkRole('usr'), 'UsuarioCtrl:modificar')->name('runModifUsuario');
    $app->post('/cambiar-imagen', checkRole('usr'), 'UsuarioCtrl:cambiarImagen')->name('runModifImgUsuario');
    $app->get('/cambiar-clave', checkRole('usr'), 'UsuarioCtrl:verCambiarClave')->name('shwModifClvUsuario');
    $app->post('/cambiar-clave', checkRole('usr'), 'UsuarioCtrl:cambiarClave')->name('runModifClvUsuario');
    $app->get('/eliminar', checkRole('usr'), 'UsuarioCtrl:verEliminar')->name('shwElimiUsuario');
    $app->post('/eliminar', checkRole('usr'), 'UsuarioCtrl:eliminar')->name('runElimiUsuario');
});

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
    $app->get('/crear', checkRole('fnc'), 'PropuestaCtrl:verCrear')->name('shwCrearPropues');
    $app->post('/crear', checkRole('fnc'), 'PropuestaCtrl:crear')->name('runCrearPropues');
    $app->get('/:idPro', 'PropuestaCtrl:ver')->name('shwPropues');
    $app->post('/:idPro/votar', checkRole('usr'), 'PropuestaCtrl:votar')->name('runVotarPropues');
    $app->post('/:idPro/cambiar-privacidad', checkRole('usr'), 'PropuestaCtrl:cambiarPrivacidad')->name('runModifPrvPropues');
    $app->get('/:idPro/modificar', checkRole(['fnc', 'mod']), 'PropuestaCtrl:verModificar')->name('shwModifPropues');
    $app->post('/:idPro/modificar', checkRole(['fnc', 'mod']), 'PropuestaCtrl:modificar')->name('runModifPropues');
    $app->post('/:idPro/eliminar', checkRole(['fnc', 'mod']), 'PropuestaCtrl:eliminar')->name('runElimiPropues');
});

$app->group('/problematica', function () use ($app) {
    $app->get('/crear', checkRole('usr'), 'ProblematicaCtrl:verCrear')->name('shwCrearProblem');
    $app->post('/crear', checkRole('usr'), 'ProblematicaCtrl:crear')->name('runCrearProblem');
    $app->get('/:idPro', 'ProblematicaCtrl:ver')->name('shwProblem');
    $app->post('/:idPro/votar', checkRole('usr'), 'ProblematicaCtrl:votar')->name('runVotarProblem');
});

$app->group('/documento', function () use ($app) {
    $app->get('/crear', checkRole('fnc'), 'DocumentoCtrl:verCrear')->name('shwCrearDocumen');
    $app->post('/crear', checkRole('fnc'), 'DocumentoCtrl:crear')->name('runCrearDocumen');
    $app->get('/:idDoc', 'DocumentoCtrl:ver')->name('shwDocumen');
    $app->get('/:idDoc/v/:idVer', 'DocumentoCtrl:ver')->name('shwVerDocumen');
    $app->get('/:idDoc/modificar', checkRole(['fnc', 'mod']), 'DocumentoCtrl:verModificar')->name('shwModifDocumen');
    $app->post('/:idDoc/modificar', checkRole(['fnc', 'mod']), 'DocumentoCtrl:modificar')->name('runModifDocumen');
    $app->get('/:idDoc/nueva-version', checkRole('fnc'), 'DocumentoCtrl:verNuevaVersion')->name('shwNuVerDocumen');
    $app->post('/:idDoc/nueva-version', checkRole('fnc'), 'DocumentoCtrl:nuevaVersion')->name('runNuVerDocumen');
    $app->post('/:idDoc/eliminar', checkRole(['fnc', 'mod']), 'DocumentoCtrl:eliminar')->name('runElimiDocumen');
});

$app->group('/partido', function () use ($app) {
    $app->get('', 'PartidoCtrl:listar')->name('shwListaPartido');
    $app->get('/crear', checkRole('fnc'), 'PartidoCtrl:verCrear')->name('shwCrearPartido');
    $app->post('/crear', checkRole('fnc'), 'PartidoCtrl:crear')->name('runCrearPartido');
    $app->post('/dejar', checkRole('usr'), 'PartidoCtrl:dejar')->name('runDejarPartido');
    $app->post('/:idPar/unirse', checkRole('usr'), 'PartidoCtrl:unirse')->name('runUnirsePartido');
    $app->get('/:idPar/modificar', checkRole('fnc'), 'PartidoCtrl:verModificar')->name('shwModifPartido');
    $app->post('/:idPar/modificar', checkRole('fnc'), 'PartidoCtrl:modificar')->name('runModifPartido');
    $app->post('/:idPar/cambiar-imagen', checkRole('fnc'), 'PartidoCtrl:cambiarImagen')->name('runModifImgPartido');
});

session_cache_limiter(false);
session_start();
$app->run();
