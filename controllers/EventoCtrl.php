<?php use Augusthur\Validation as Validate;

class EventoCtrl extends Controller {

    public function ver($idEve) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idEve, new Validate\Rule\NumNatural());
        $evento = Evento::with(['contenido.tags', 'comentarios'])->findOrFail($idEve);
        $contenido = $evento->contenido;
        $participe = $evento->usuarios()->where('usuario_id', $this->session->user('id'))->first();
        $participantes = $evento->usuarios()->where('publico', '1')->get()->toArray();
        $comentarios = $evento->comentarios->toArray();
        $datosEven = array_merge($contenido->toArray(), $evento->toArray());
        $datosEven['presentes_count'] = $evento->usuarios()->where('presente', '1')->count();
        $datosEven['ausentes_count'] = $evento->usuarios()->where('presente', '0')->count();
        $datosPart = $participe ? $participe->pivot->toArray() : null;
        $this->render('contenido/evento/ver.twig', ['evento' => $datosEven,
                                                    'comentarios' =>  $comentarios,
                                                    'participacion' => $datosPart,
                                                    'participantes' => $participantes]);
    }

    public function participar($idEve) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idEve', new Validate\Rule\NumNatural())
            ->addFilter('presente', FilterFactory::booleanFilter())
            ->addFilter('publico', FilterFactory::booleanFilter());
        $req = $this->request;
        $data = array_merge(array('idEve' => $idEve), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $evento = Evento::findOrFail($idEve);
        $hoy = Carbon\Carbon::now();
        if ($hoy->gt($evento->fecha)) {
            throw new TurnbackException('El evento ya ha ocurrido.');
        }
        $sumaPost = $vdt->getData('presente')? 3: 1;
        $participe = $evento->usuarios()->where('usuario_id', $usuario->id)->first();
        if (is_null($participe)) {
            $evento->usuarios()->attach($usuario->id, ['presente' => $vdt->getData('presente'),
                                                       'publico' => $vdt->getData('publico')]);
        } else {
            $participe->pivot->presente = $vdt->getData('presente');
            $participe->pivot->publico = $vdt->getData('publico');
            $participe->pivot->save();
            $sumaPost -= $participe->pivot->presente? 3: 1;
        }
        if ($sumaPost != 0) {
            $evento->contenido()->increment('puntos', $sumaPost);
        }
        $this->flash('success', 'Su participación fue registrada exitosamente.');
        $this->redirectTo('shwEvento', array('idEve' => $evento->id));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/evento/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarEvento($req->post());
        $autor = $this->session->getUser();
        $evento = new Evento;
        $evento->cuerpo = $vdt->getData('cuerpo');
        $evento->lugar = $vdt->getData('lugar');
        $evento->fecha = Carbon\Carbon::parse($vdt->getData('fecha'));
        $evento->save();
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($evento);
        $partido = $autor->partido;
        if (isset($partido) && $vdt->getData('asociar')) {
            $contenido->impulsor()->associate($partido);
        }
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        $log = UserlogCtrl::createLog('newEventoo', $autor->id, $evento);
        if ($contenido->impulsor) {
            NotificacionCtrl::createNotif($partido->afiliados()->lists('id'), $log);
        }
        $this->flash('success', 'Su evento fue creado exitosamente.');
        $this->redirectTo('shwEvento', array('idEve' => $evento->id));
    }

    public function verModificar($idEve) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idEve, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $evento = Evento::with('contenido.tags')->findOrFail($idEve);
        $contenido = $evento->contenido;
        $datos = array_merge($contenido->toArray(), $evento->toArray());
        $this->render('contenido/evento/modificar.twig', array('evento' => $datos,
                                                               'categorias' => $categorias));
    }

    public function modificar($idEve) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idEve, new Validate\Rule\NumNatural());
        $evento = Evento::with('contenido')->findOrFail($idEve);
        $contenido = $evento->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarEvento($req->post());
        $evento->cuerpo = $vdt->getData('cuerpo');
        $evento->lugar = $vdt->getData('lugar');
        $evento->fecha = Carbon\Carbon::parse($vdt->getData('fecha'));
        $evento->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->categoria_id = $vdt->getData('categoria');
        if ($contenido->impulsor xor $vdt->getData('asociar')) {
            $partido = $usuario->partido;
            if ($partido && $vdt->getData('asociar')) {
                $contenido->impulsor()->associate($partido);
            } else {
                $contenido->impulsor()->dissociate();
            }
        }
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        $log = UserlogCtrl::createLog('modEventoo', $usuario->id, $evento);
        NotificacionCtrl::createNotif($evento->usuarios()->lists('usuario_id'), $log);
        $this->flash('success', 'Los datos del evento fueron modificados exitosamente.');
        $this->redirectTo('shwEvento', array('idEve' => $idEve));
    }

    public function eliminar($idEve) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idEve, new Validate\Rule\NumNatural());
        $evento = Evento::with(['contenido', 'comentarios.votos'])->findOrFail($idEve);
        $usuarios = $evento->usuarios()->lists('usuario_id');
        $evento->delete();
        $log = UserlogCtrl::createLog('delEventoo', $this->session->user('id'), $evento);
        NotificacionCtrl::createNotif($usuarios, $log);
        $this->flash('success', 'El evento ha sido eliminado exitosamente.');
        $this->redirectTo('shwIndex');
    }

    private function validarEvento($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('lugar', new Validate\Rule\MinLength(4))
            ->addRule('lugar', new Validate\Rule\MaxLength(128))
            ->addRule('fecha', new Validate\Rule\Date('Y-m-d H:i:s'))
            ->addRule('tags', new Validate\Rule\Required())
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML())
            ->addFilter('asociar', FilterFactory::booleanFilter())
            ->addFilter('tags', FilterFactory::explode(','));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
