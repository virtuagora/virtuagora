<?php use Augusthur\Validation as Validate;

class PartidoCtrl extends RMRController {

    protected $mediaTypes = array('json', 'view');
    protected $properties = array('id', 'nombre', 'acronimo', 'fecha_fundacion', 'created_at');
    protected $searchable = true;

    public function queryModel($meth, $repr) {
        return Partido::query();
    }

    public function executeListCtrl($paginator) {
        $partidos = $paginator->rows;
        $nav = $paginator->links;
        $this->render('partido/listar.twig', array('partidos' => $partidos->toArray(),
                                                   'nav' => $nav));
    }

    public function executeGetCtrl($partido) {
        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($partido->contenidos(), $url, $req->get());
        $contenidos = $paginator->rows->toArray();
        $nav = $paginator->links;
        $jefes = $partido->afiliados()->where('es_jefe', true)->get()->toArray();
        $datos = $partido->toArray();
        $datos['afiliados_count'] = $partido->afiliados()->count();
        $this->render('partido/ver.twig', array('partido' => $datos,
                                                'jefes' => $jefes,
                                                'contenidos' => $contenidos,
                                                'nav' => $nav));
    }

    public function verCrear() {
        $this->render('partido/crear.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPartido($req->post());
        $usuario = $this->session->getUser();
        if ($usuario->partido_id) {
            throw new TurnbackException('No es posible crear un grupo si ya está afilado a otro.');
        }
        $partido = new Partido;
        $partido->nombre = $vdt->getData('nombre');
        $partido->acronimo = $vdt->getData('acronimo');
        $partido->descripcion = $vdt->getData('descripcion');
        $partido->fundador = $vdt->getData('fundador');
        $partido->fecha_fundacion = $vdt->getData('fecha');
        $partido->creador_id = $this->session->user('id');
        $partido->creador()->associate($usuario);
        $partido->save();
        $contacto = new Contacto;
        $contacto->email = $vdt->getData('email');
        $contacto->web = $vdt->getData('url');
        $contacto->telefono = $vdt->getData('telefono');
        $contacto->contactable()->associate($partido);
        $contacto->save();
        UserlogCtrl::createLog('newPartido', $usuario->id, $partido);
        ImageManager::crearImagen('partido', $partido->id, $partido->nombre, array(32, 64, 160));
        $this->session->update();
        $this->flash('success', 'El grupo '.$partido->nombre.' fue creado exitosamente.');
        $this->redirectTo('shwListaPartido');
    }

    public function unirse($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::findOrFail($idPar);
        $usuario = $this->session->getUser();
        if ($usuario->partido) {
            throw new TurnbackException('Usted ya está afiliado a otro grupo.');
        }
        $usuario->partido()->associate($partido);
        $usuario->save();
        $notificados = $partido->afiliados()->where('es_jefe', 1)->lists('id');
        $log = UserlogCtrl::createLog('joiPartido', $usuario->id, $partido);
        NotificacionCtrl::createNotif($notificados, $log);
        $this->session->update($usuario);
        $this->flash('success', 'Se ha unido al grupo '.$partido->nombre.'.');
        $this->redirectTo('shwListaPartido');
    }

    public function dejar() {
        $usuario = $this->session->getUser();
        $partido = $usuario->partido;
        if (!$partido) {
            throw new BearableException('Usted no pertenece a ningún grupo.');
        } else if ($partido->creador_id == $usuario->id) {
            throw new BearableException('Usted no puede dejar el grupo que creó.');
        }
        $usuario->partido()->dissociate();
        $usuario->es_jefe = false;
        $usuario->save();
        $notificados = $partido->afiliados()->where('es_jefe', 1)->lists('id');
        $log = UserlogCtrl::createLog('lefPartido', $usuario->id, $partido);
        NotificacionCtrl::createNotif($notificados, $log);
        $this->session->update($usuario);
        $this->flash('success', 'Ha dejado el grupo '.$partido->nombre.'.');
        $this->redirectTo('shwListaPartido');
    }

    public function verModificar($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::with('contacto')->findOrFail($idPar);
        $datosPartido = $partido->toArray();
        $datosPartido['contacto'] = $partido->contacto ? $partido->contacto->toArray() : null;
        $this->render('partido/modificar.twig', array('partido' => $datosPartido));
    }

    public function modificar($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::with('contacto')->findOrFail($idPar);
        $usuario = $this->session->getUser();
        echo 'la1';

        if ($usuario->partido_id != $partido->id || !$usuario->es_jefe) {
            throw new BearableException('Debe ser jefe del grupo para poder modificarlo.');
        }
        echo 'la2';

        $req = $this->request;
        $vdt = $this->validarPartido($req->post());
        $partido->nombre = $vdt->getData('nombre');
        $partido->acronimo = $vdt->getData('acronimo');
        $partido->descripcion = $vdt->getData('descripcion');
        $partido->fundador = $vdt->getData('fundador');
        $partido->fecha_fundacion = $vdt->getData('fecha');
        $partido->save();
        $contacto = $partido->contacto;
        $contacto->email = $vdt->getData('email');
        $contacto->web = $vdt->getData('url');
        $contacto->telefono = $vdt->getData('telefono');
        $contacto->save();
        echo 'la3';

        $this->flash('success', 'Los datos del grupo fueron modificados exitosamente.');
        echo 'la4';

        $this->redirect($this->request->getReferrer());
    }

    public function cambiarImagen($idPar) {
        ImageManager::cambiarImagen('partido', $idPar, array(32, 64, 160));
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function verEliminar($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::findOrFail($idPar);
        $this->render('partido/eliminar.twig', array('partido' => $partido->toArray()));
    }

    public function eliminar($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::with('contacto')->findOrFail($idPar);
        if (!$this->session->check($partido->creador_id)) {
            throw new BearableException('Un grupo solo puede ser eliminado por su creador.');
        }
        $notificados = $partido->afiliados()->lists('id');
        $partido->delete();
        $log = UserlogCtrl::createLog('delPartido', $this->session->user('id'), $partido);
        NotificacionCtrl::createNotif($notificados, $log);
        $this->session->update();
        $this->flash('success', 'El grupo ha sido eliminado exitosamente.');
        $this->redirectTo('shwIndex');
    }

    public function verCambiarRol($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::findOrFail($idPar);
        $jefes = $partido->afiliados()->where('es_jefe', 1)->get();
        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($partido->afiliados()->where('es_jefe', 0), $url, $req->get());
        $afiliados = $paginator->rows;
        $nav = $paginator->links;
        $this->render('partido/gestionar-roles.twig', ['partido' => $partido->toArray(),
                                                       'jefes' => $jefes->toArray(),
                                                       'afiliados' => $afiliados->toArray(),
                                                       'nav' => $nav]);
    }

    public function cambiarRol($idPar) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPar', new Validate\Rule\NumNatural())
            ->addRule('idUsu', new Validate\Rule\NumNatural())
            ->addRule('jefe', new Validate\Rule\InArray(array(1, 0)));
        $req = $this->request;
        $data = array_merge(array('idPar' => $idPar), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $partido = Partido::findOrFail($vdt->getData('idPar'));
        // TODO que pasa si el usuario no está en el partido?
        $usuario = Usuario::where(array('id' => $vdt->getData('idUsu'),
                                        'partido_id' => $vdt->getData('idPar')))->first();
        if ($usuario->id == $partido->creador_id) {
            throw new TurnbackException('No se puede cambiar el rol del creador del grupo.');
        } else if (is_null($usuario)) {
            throw new TurnbackException($usuario->identidad.' no pertenece al grupo.');
        } else if (!($usuario->es_jefe xor $vdt->getData('jefe'))) {
            throw new TurnbackException('Configuración inválida.');
        }
        $usuario->es_jefe = $vdt->getData('jefe');
        $usuario->save();
        $notificados = $partido->afiliados()->lists('id');
        $log = UserlogCtrl::createLog($usuario->es_jefe? 'newJefPart': 'delJefPart', $usuario->id, $partido);
        NotificacionCtrl::createNotif($notificados, $log);
        $msg = $usuario->es_jefe? ' comenzó a ': ' dejó de ';
        $this->flash('success', $usuario->identidad.$msg.'ser jefe del grupo.');
        $this->redirectTo('shwModifRolPartido', array('idPar' => $idPar));
    }

    private function validarPartido($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('acronimo', new Validate\Rule\Alpha())
            ->addRule('acronimo', new Validate\Rule\MinLength(2))
            ->addRule('acronimo', new Validate\Rule\MaxLength(8))
            ->addRule('descripcion', new Validate\Rule\MinLength(4))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512))
            ->addRule('fundador', new Validate\Rule\Alpha(array(' ')))
            ->addRule('fundador', new Validate\Rule\MaxLength(32))
            ->addRule('fecha', new Validate\Rule\Date())
            ->addRule('url', new Validate\Rule\URL())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('telefono', new Validate\Rule\Telephone())
            ->addOptional('fundador')
            ->addOptional('fecha')
            ->addOptional('url')
            ->addOptional('email')
            ->addOptional('telefono')
            ->addFilter('fundador', FilterFactory::emptyToNull())
            ->addFilter('fecha', FilterFactory::emptyToNull())
            ->addFilter('url', FilterFactory::emptyToNull())
            ->addFilter('email', FilterFactory::emptyToNull())
            ->addFilter('telefono', FilterFactory::emptyToNull());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
