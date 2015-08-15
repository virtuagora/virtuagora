<?php
// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => false,
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension(), new ExtendedTwig());

// Prepare singletons
$app->container->singleton('session', function () use ($app) {
    return new SessionManager($app->getMode());
});

$app->container->singleton('translator', function () {
    $trans = new Symfony\Component\Translation\Translator('es');
    $trans->setFallbackLocale('es');
    $trans->addLoader('php', new Symfony\Component\Translation\Loader\PhpFileLoader());
    $trans->addResource('php', __DIR__.'/../locales/es.php', 'es');
    return $trans;
});

$app->api = false;

// Prepare error handler
$app->error(function (Exception $e) use ($app) {
    if ($app->api) {
        // TODO setar codigo de error correcto.
        $msg = array('code' => $e->getCode(), 'message' => $e->getMessage());
        if ($e instanceof TurnbackException) {
            $msg['errors'] = $e->getErrors();
        }
        echo json_encode($msg);
    } else {
        if ($e instanceof TurnbackException) {
            $app->flash('errors', $e->getErrors());
            ob_end_clean();
            $app->redirect($app->request->getReferrer());
        } else if ($e instanceof BearableException) {
            $app->render('misc/error.twig', array('mensaje' => $e->getMessage()), $e->getCode());
        } else if ($e instanceof Illuminate\Database\Eloquent\ModelNotFoundException) {
            $app->notFound();
        } else if ($e instanceof Illuminate\Database\QueryException && $e->getCode() == 23000) {
            $app->render('misc/error.twig', array('mensaje' => 'La información ingresada es inconsistente.'), 400);
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
$checkNoSession = function () use ($app) {
    if ($app->session->check()) {
        $app->redirect($app->request->getRootUri());
    }
};

$checkRole = function ($role) use ($app) {
    return function () use ($role, $app) {
        if (is_array($role)) {
            $rejected = empty($app->session->grantedRoles($role));
        } else {
            $rejected = !$app->session->hasRole($role);
        }
        if ($rejected) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
};

$checkAdminAuth = function ($action) use ($app) {
    return function () use ($action, $app) {
        if (!$app->session->isAdminAllowedTo($action)) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
};

$checkModifyAuth = function ($resource, $moderable = true) use ($app) {
    return function ($route) use ($resource, $moderable, $app) {
        $params = $route->getParams();
        $idRes = reset($params);
        $idUsr = $app->session->user('id');
        $query = call_user_func($resource.'::modifiableBy', $idUsr);
        if (is_null($query->find($idRes)) && !($moderable && $app->session->isAdminAllowedTo(1))) {
            throw new BearableException('No tiene permiso para realizar esta acción', 403);
        }
    };
};

/* NO ES NECESARIO POR AHORA
function checkUserAuth($action, $checkMod = false) {
    global $app;
    $roles = $app->session->rolesAllowedTo($action);
    if ($checkMod && count($roles) == 1 && $roles[0] == 'mod') {
        return $checkAdminAuth('admConteni');
    } else {
        return checkRole($roles);
    }
}
*/

// Prepare dispatcher
$app->get('/captcha', function () use ($app) {
    $builder = new Gregwar\Captcha\CaptchaBuilder;
    $builder->build();
    $app->response->headers->set('Content-Type', 'image/jpeg');
    $app->response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    $app->flash('captcha', $builder->getPhrase());
    $builder->output();
})->name('shwCaptcha');

//$app->get('/userlog', 'UserlogCtrl:listar')->name('shwListaUserlog');

$app->get('/', 'PortalCtrl:verIndex')->name('shwIndex');
$app->get('/portal', 'PortalCtrl:verPortal')->name('shwPortal');
$app->get('/tos', 'PortalCtrl:verTos')->name('shwTos');
$app->get('/login', $checkNoSession, 'PortalCtrl:verLogin')->name('shwLogin');
$app->post('/login', $checkNoSession, 'PortalCtrl:login')->name('runLogin');
$app->post('/logout', 'PortalCtrl:logout')->name('runLogout');
$app->get('/registro', $checkNoSession, 'PortalCtrl:verRegistrar')->name('shwCrearUsuario');
$app->post('/registro', $checkNoSession, 'PortalCtrl:registrar')->name('runCrearUsuario');
$app->get('/validar/:idUsu/:token', 'PortalCtrl:verificarEmail')->name('runValidUsuario');

$app->get('/notificacion', $checkRole('usr'), 'NotificacionCtrl:listar')->name('shwListaNotific');
$app->post('/notificacion/eliminar', $checkRole('usr'), 'NotificacionCtrl:eliminar')->name('runElimiNotific');

$app->get('/tag', 'TagCtrl:listar')->name('shwTag');

$app->get('/contenido/:idCon', 'ContenidoCtrl:ver')->name('shwConteni');
$app->get('/contenido', 'ContenidoCtrl:listar')->name('shwListaConteni');

$app->get('/organismo', 'OrganismoCtrl:listar')->name('shwListaOrganis');
$app->get('/organismo/:idOrg', 'OrganismoCtrl:ver')->name('shwOrganis');

$app->get('/usuario/:idUsu', 'UsuarioCtrl:ver')->name('shwUsuario');
$app->get('/usuario/:idUsu/imagen/:res', 'UsuarioCtrl:verImagen')->name('shwImgUsuario');
$app->get('/usuario', 'UsuarioCtrl:listar')->name('shwListaUsuario');

$app->group('/comentario', function () use ($app, $checkRole) {
    $app->get('', 'ComentarioCtrl:listar')->name('shwListaComenta');
    $app->post('/comentar/:tipoRaiz/:idRaiz', $checkRole('usr'), 'ComentarioCtrl:comentar')->name('runComentar');
    $app->get('/:idCom', 'ComentarioCtrl:ver')->name('shwComenta');
    $app->post('/:idCom/votar', $checkRole('usr'), 'ComentarioCtrl:votar')->name('runVotarComenta');
});

$app->group('/perfil', function () use ($app, $checkRole) {
    $app->get('/modificar', $checkRole('usr'), 'UsuarioCtrl:verModificar')->name('shwModifUsuario');
    $app->post('/modificar', $checkRole('usr'), 'UsuarioCtrl:modificar')->name('runModifUsuario');
    $app->post('/cambiar-imagen', $checkRole('usr'), 'UsuarioCtrl:cambiarImagen')->name('runModifImgUsuario');
    $app->get('/cambiar-clave', $checkRole('usr'), 'UsuarioCtrl:verCambiarClave')->name('shwModifClvUsuario');
    $app->post('/cambiar-clave', $checkRole('usr'), 'UsuarioCtrl:cambiarClave')->name('runModifClvUsuario');
    $app->get('/eliminar', $checkRole('usr'), 'UsuarioCtrl:verEliminar')->name('shwElimiUsuario');
    $app->post('/eliminar', $checkRole('usr'), 'UsuarioCtrl:eliminar')->name('runElimiUsuario');
});

$app->group('/admin', function () use ($app, $checkRole, $checkAdminAuth) {
    $app->get('/organismo', $checkRole('mod'), 'OrganismoCtrl:listarInterno')->name('shwAdmOrganis');
    $app->get('/organismo/:idOrg/modificar', $checkAdminAuth(3), 'OrganismoCtrl:verModificar')->name('shwModifOrganis');
    $app->post('/organismo/:idOrg/modificar', $checkAdminAuth(3), 'OrganismoCtrl:modificar')->name('runModifOrganis');
    $app->post('/organismo/:idOrg/cambiar-imagen', $checkAdminAuth(3), 'OrganismoCtrl:cambiarImagen')->name('runModifImgOrganis');
    $app->post('/organismo/:idOrg/eliminar', $checkAdminAuth(3), 'OrganismoCtrl:eliminar')->name('runElimiOrganis');
    $app->get('/organismo/crear', $checkAdminAuth(3), 'OrganismoCtrl:verCrear')->name('shwCrearOrganis');
    $app->post('/organismo/crear', $checkAdminAuth(3), 'OrganismoCtrl:crear')->name('runCrearOrganis');
    $app->get('/organismo/:idOrg/funcionario', $checkAdminAuth(4), 'AdminCtrl:verAdminFuncionarios')->name('shwAdmFuncion');
    $app->post('/organismo/:idOrg/funcionario', $checkAdminAuth(4), 'AdminCtrl:adminFuncionarios')->name('runAdmFuncion');

    $app->post('/sancionar/:idUsu', $checkAdminAuth(1), 'AdminCtrl:sancUsuario')->name('runSanUsuario');
    $app->get('/verificar', $checkAdminAuth(7), 'AdminCtrl:verVerifCiudadano')->name('shwAdmVrfUsuario');
    $app->post('/verificar', $checkAdminAuth(7), 'AdminCtrl:verifCiudadano')->name('runAdmVrfUsuario');
    $app->get('/ajustes', $checkAdminAuth(2), 'AdminCtrl:verAdminAjustes')->name('shwAdmAjuste');
    $app->post('/ajustes', $checkAdminAuth(2), 'AdminCtrl:adminAjustes')->name('runAdmAjuste');
    $app->get('/moderador/crear', $checkAdminAuth(6), 'PatrullaCtrl:verCrearModeradores')->name('shwCrearModerad');
    $app->post('/moderador/crear', $checkAdminAuth(6), 'PatrullaCtrl:crearModeradores')->name('runCrearModerad');

    $app->get('/patrulla', $checkRole('mod'), 'PatrullaCtrl:listar')->name('shwAdmPatrull');
    $app->get('/patrulla/crear', $checkAdminAuth(5), 'PatrullaCtrl:verCrear')->name('shwCrearPatrull');
    $app->post('/patrulla/crear', $checkAdminAuth(5), 'PatrullaCtrl:crear')->name('runCrearPatrull');
    $app->get('/patrulla/:idPat', $checkRole('mod'), 'PatrullaCtrl:ver')->name('shwPatrull'); // TODO crear funcionalidad de vista
    $app->post('/patrulla/:idPat/modificar', $checkAdminAuth(5), 'PatrullaCtrl:modificar')->name('runModifPatrull');
    $app->post('/patrulla/:idPat/eliminar', $checkAdminAuth(5), 'PatrullaCtrl:eliminar')->name('runElimiPatrull');
    $app->get('/patrulla/:idPat/cambiar-poder', $checkAdminAuth(5), 'PatrullaCtrl:verCambiarPoder')->name('shwModifPodPatrull');
    $app->post('/patrulla/:idPat/cambiar-poder', $checkAdminAuth(5), 'PatrullaCtrl:cambiarPoder')->name('runModifPodPatrull');
    $app->get('/patrulla/:idPat', $checkAdminAuth(6), 'PatrullaCtrl:ver')->name('shwAdmModerad');
    $app->post('/patrulla/:idPat/moderador', $checkAdminAuth(6), 'PatrullaCtrl:adminModeradores')->name('runAdmModerad');
});

$app->group('/propuesta', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('/crear', $checkRole('fnc'), 'PropuestaCtrl:verCrear')->name('shwCrearPropues');
    $app->post('/crear', $checkRole('fnc'), 'PropuestaCtrl:crear')->name('runCrearPropues');
    $app->get('/:idPro', 'PropuestaCtrl:ver')->name('shwPropues');
    $app->post('/:idPro/votar', $checkRole('usr'), 'PropuestaCtrl:votar')->name('runVotarPropues');
    $app->post('/:idPro/cambiar-privacidad', $checkRole('usr'), 'PropuestaCtrl:cambiarPrivacidad')->name('runModifPrvPropues');
    $app->get('/:idPro/modificar', $checkModifyAuth('Propuesta'), 'PropuestaCtrl:verModificar')->name('shwModifPropues');
    $app->post('/:idPro/modificar', $checkModifyAuth('Propuesta'), 'PropuestaCtrl:modificar')->name('runModifPropues');
    $app->post('/:idPro/eliminar', $checkModifyAuth('Propuesta'), 'PropuestaCtrl:eliminar')->name('runElimiPropues');
});

$app->group('/problematica', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('/crear', $checkRole('usr'), 'ProblematicaCtrl:verCrear')->name('shwCrearProblem');
    $app->post('/crear', $checkRole('usr'), 'ProblematicaCtrl:crear')->name('runCrearProblem');
    $app->get('/:idPro', 'ProblematicaCtrl:ver')->name('shwProblem');
    $app->post('/:idPro/votar', $checkRole('usr'), 'ProblematicaCtrl:votar')->name('runVotarProblem');
    $app->get('/:idPro/modificar', $checkModifyAuth('Problematica'), 'ProblematicaCtrl:verModificar')->name('shwModifProblem');
    $app->post('/:idPro/modificar', $checkModifyAuth('Problematica'), 'ProblematicaCtrl:modificar')->name('runModifProblem');
    $app->post('/:idPro/eliminar', $checkModifyAuth('Problematica'), 'ProblematicaCtrl:eliminar')->name('runElimiProblem');
});

$app->group('/documento', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('/crear', $checkRole('fnc'), 'DocumentoCtrl:verCrear')->name('shwCrearDocumen');
    $app->post('/crear', $checkRole('fnc'), 'DocumentoCtrl:crear')->name('runCrearDocumen');
    $app->get('/:idDoc', 'DocumentoCtrl:ver')->name('shwDocumen');
    $app->get('/:idDoc/v/:idVer', 'DocumentoCtrl:ver')->name('shwVerDocumen');
    $app->get('/:idDoc/modificar', $checkModifyAuth('Documento'), 'DocumentoCtrl:verModificar')->name('shwModifDocumen');
    $app->post('/:idDoc/modificar', $checkModifyAuth('Documento'), 'DocumentoCtrl:modificar')->name('runModifDocumen');
    $app->get('/:idDoc/nueva-version', $checkModifyAuth('Documento', false), 'DocumentoCtrl:verNuevaVersion')->name('shwNuVerDocumen');
    $app->post('/:idDoc/nueva-version', $checkModifyAuth('Documento', false), 'DocumentoCtrl:nuevaVersion')->name('runNuVerDocumen');
    $app->post('/:idDoc/eliminar', $checkModifyAuth('Documento'), 'DocumentoCtrl:eliminar')->name('runElimiDocumen');
});

$app->group('/novedad', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('/crear', $checkRole('fnc'), 'NovedadCtrl:verCrear')->name('shwCrearNovedad');
    $app->post('/crear', $checkRole('fnc'), 'NovedadCtrl:crear')->name('runCrearNovedad');
    $app->get('/:idNov', 'NovedadCtrl:ver')->name('shwNovedad');
    $app->get('/:idNov/modificar', $checkModifyAuth('Novedad'), 'NovedadCtrl:verModificar')->name('shwModifNovedad');
    $app->post('/:idNov/modificar', $checkModifyAuth('Novedad'), 'NovedadCtrl:modificar')->name('runModifNovedad');
    $app->post('/:idNov/eliminar', $checkModifyAuth('Novedad'), 'NovedadCtrl:eliminar')->name('runElimiNovedad');
});

$app->group('/evento', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('/crear', $checkRole('fnc'), 'EventoCtrl:verCrear')->name('shwCrearEvento');
    $app->post('/crear', $checkRole('fnc'), 'EventoCtrl:crear')->name('runCrearEvento');
    $app->get('/:idEve', 'EventoCtrl:ver')->name('shwEvento');
    $app->post('/:idEve/participar', $checkRole('usr'), 'EventoCtrl:participar')->name('runPartiEvento');
    $app->get('/:idEve/modificar', $checkModifyAuth('Evento'), 'EventoCtrl:verModificar')->name('shwModifEvento');
    $app->post('/:idEve/modificar', $checkModifyAuth('Evento'), 'EventoCtrl:modificar')->name('runModifEvento');
    $app->post('/:idEve/eliminar', $checkModifyAuth('Evento'), 'EventoCtrl:eliminar')->name('runElimiEvento');
});

$app->group('/partido', function () use ($app, $checkRole, $checkModifyAuth) {
    $app->get('', 'PartidoCtrl:listar')->name('shwListaPartido');
    $app->get('/crear', $checkRole('usr'), 'PartidoCtrl:verCrear')->name('shwCrearPartido');
    $app->post('/crear', $checkRole('usr'), 'PartidoCtrl:crear')->name('runCrearPartido');
    $app->post('/dejar', $checkRole('usr'), 'PartidoCtrl:dejar')->name('runDejarPartido');
    $app->get('/:idPar', 'PartidoCtrl:ver')->name('shwPartido');
    $app->post('/:idPar/unirse', $checkRole('usr'), 'PartidoCtrl:unirse')->name('runUnirsePartido');
    $app->get('/:idPar/modificar', $checkModifyAuth('Partido'), 'PartidoCtrl:verModificar')->name('shwModifPartido');
    $app->post('/:idPar/modificar', $checkModifyAuth('Partido'), 'PartidoCtrl:modificar')->name('runModifPartido');
    $app->post('/:idPar/cambiar-imagen', $checkModifyAuth('Partido'), 'PartidoCtrl:cambiarImagen')->name('runModifImgPartido');
    $app->get('/:idPar/cambiar-rol', $checkModifyAuth('Partido', false), 'PartidoCtrl:verCambiarRol')->name('shwModifRolPartido');
    $app->post('/:idPar/cambiar-rol', $checkModifyAuth('Partido', false), 'PartidoCtrl:cambiarRol')->name('runModifRolPartido');
    $app->get('/:idPar/eliminar', $checkModifyAuth('Partido', false), 'PartidoCtrl:verEliminar')->name('shwElimiPartido');
    $app->post('/:idPar/eliminar', $checkModifyAuth('Partido', false), 'PartidoCtrl:eliminar')->name('runElimiPartido');
});
