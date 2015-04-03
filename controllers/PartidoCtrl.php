<?php use Augusthur\Validation as Validate;

class PartidoCtrl extends RMRController {

    protected $mediaTypes = array('json', 'view');
    protected $properties = array('id', 'nombre', 'acronimo', 'fecha_fundacion', 'created_at');

    public function queryModel() {
        return Partido::query();
    }

    public function executeListCtrl($paginator) {
        $partidos = $paginator->rows;
        $nav = $paginator->links;
        $this->render('partido/listar.twig', array('partidos' => $partidos->toArray(),
                                                   'nav' => $nav));
    }

    public function executeGetCtrl($partido) {
        $this->render('partido/ver.twig', array('partido' => $partido->toArray()));
    }

    public function verCrear() {
        $this->render('partido/crear.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPartido($req->post());
        $usuario = $this->session->getUser();
        if ($usuario->partido_id) {
            throw new TurnbackException('No es posible crear un partido si ya está afilado a otro.');
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
        $accion = new Accion;
        $accion->tipo = 'new_partido';
        $accion->objeto()->associate($partido);
        $accion->actor()->associate($usuario);
        $accion->save();
        ImageManager::crearImagen('partido', $partido->id, $partido->nombre, array(32, 64, 160));
        $this->session->update();
        $this->flash('success', 'El partido '.$partido->nombre.' fue creado exitosamente.');
        $this->redirectTo('shwListaPartido');
    }

    public function unirse($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::findOrFail($idPar);
        $usuario = $this->session->getUser();
        if ($usuario->partido) {
            throw new TurnbackException('Usted ya está afiliado a otro partido.');
        }
        $usuario->partido()->associate($partido);
        $usuario->save();
        $accion = new Accion;
        $accion->tipo = 'joi_partido';
        $accion->objeto()->associate($partido);
        $accion->actor()->associate($usuario);
        $accion->save();
        $this->session->update($usuario);
        $this->flash('success', 'Se ha unido al partido '.$partido->nombre.'.');
        $this->redirectTo('shwListaPartido');
    }

    public function dejar() {
        $usuario = $this->session->getUser();
        $partido = $usuario->partido;
        if (!$partido) {
            throw new BearableException('Usted no pertenece a ningún partido.');
        } else if ($partido->creador_id == $usuario->id) {
            throw new BearableException('Usted no puede dejar el partido que creó.');
        }
        $usuario->partido()->dissociate();
        $usuario->save();
        $accion = new Accion;
        $accion->tipo = 'lef_partido';
        $accion->objeto()->associate($partido);
        $accion->actor()->associate($usuario);
        $accion->save();
        $this->session->update($usuario);
        $this->flash('success', 'Ha dejado el partido '.$partido->nombre.'.');
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
        if ($usuario->partido_id != $partido->id || !$usuario->es_jefe) {
            throw new BearableException('Debe ser jefe del partido para poder modificarlo.');
        }
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
        $this->flash('success', 'Los datos del partido fueron modificados exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function cambiarImagen($idPar) {
        ImageManager::cambiarImagen('partido', $idPar, array(32, 64, 160));
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function eliminar($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::with('contacto')->findOrFail($idPar);
        if ($this->session->check($partido->creador_id)) {
            throw new BearableException('Un partido puede ser eliminado solamente por su creador.');
        }
        $partido->delete();
        $this->session->update();
        $this->flash('success', 'El partido ha sido eliminado exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function verCambiarRol($idPar) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPar, new Validate\Rule\NumNatural());
        $partido = Partido::findOrFail($idPar);
        $jefes = $partido->afiliados()->where('es_jefe', 1)->get();
        $afiliados = $partido->afiliados()->where('es_jefe', 0)->get();
        $this->render('partido/cambiar-rol.twig', array('partido' => $partido->toArray(),
                                                        'jefes' => $jefes->toArray(),
                                                        'afiliados' => $afiliados->toArray()));
    }

    public function cambiarRol($idPar) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPar', new Validate\Rule\NumNatural())
            ->addRule('idUsr', new Validate\Rule\NumNatural())
            ->addRule('jefe', new Validate\Rule\InArray(array(1, 0)));
        $req = $this->request;
        $data = array_merge(array('idPar' => $idPar), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::where(array('id' => $vdt->getData('idUsr'),
                                        'partido_id' => $vdt->getData('idPar')))->first();
        if (is_null($usuario)) {
            throw new TurnbackException($usuario->nombre_completo.' no pertenece al partido.');
        }
        if (!($usuario->es_jefe xor $vdt->getData('jefe'))) {
            throw new TurnbackException('Configuración inválida.');
        }
        $usuario->es_jefe = $vdt->getData('jefe');
        $usuario->save();
        $accion = new Accion;
        $accion->tipo = $usuario->es_jefe ? 'new_jefe' : 'del_jefe';
        $accion->objeto()->associate($partido);
        $accion->actor()->associate($usuario);
        $accion->save();
        $msg = $usuario->es_jefe ? ' comenzó a ' : ' dejó de ';
        $this->flash('success', $usuario->nombre_completo.$msg.'ser jefe del partido.');
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
            ->addOptional('telefono');
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
