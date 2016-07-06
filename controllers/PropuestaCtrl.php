<?php use Augusthur\Validation as Validate;

class PropuestaCtrl extends Controller {

    public function ver($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(['contenido.referido', 'contenido.tags', 'comentarios'])->findOrFail($idPro);
        $contenido = $propuesta->contenido;
        $voto = $propuesta->votos()->where('usuario_id', $this->session->user('id'))->first();
        $comentarios = $propuesta->comentarios->toArray();
        $votos = $propuesta->votos()->with('usuario')->where('publico', '1')->get()->toArray();
        $datosProp = array_merge($contenido->toArray(), $propuesta->toArray());
        $datosVoto = $voto ? $voto->toArray() : null;
        $this->render('contenido/propuesta/ver.twig', ['propuesta' => $datosProp,
                                                       'comentarios' =>  $comentarios,
                                                       'voto' => $datosVoto,
                                                       'votos' => $votos]);
    }

    public function votar($idPro) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray(array(-1, 0, 1)))
            ->addRule('idPro', new Validate\Rule\NumNatural())
            ->addFilter('publico', FilterFactory::booleanFilter());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $propuesta = Propuesta::with('contenido')->findOrFail($idPro);
        $voto = VotoPropuesta::firstOrNew(array('propuesta_id' => $propuesta->id,
                                                'usuario_id' => $usuario->id));
        $cfgPtsAutr = [-1 => 0, 0 => 0, 1 => 2];
        $cfgPtsPost = [-1 => 1, 0 => 2, 1 => 3];
        $cfgCount = [-1 => 'votos_contra', 0 => 'votos_neutro', 1 => 'votos_favor'];
        $postura = $vdt->getData('postura');
        $sumaAutr = $cfgPtsAutr[$postura];
        $sumaPost = $cfgPtsPost[$postura];
        if (!$voto->exists) {
            $usuario->increment('puntos', 3);
            UserlogCtrl::createLog('votPropues', $usuario->id, $propuesta);
        } else if ($voto->postura != $postura) {
            $hoy = Carbon\Carbon::now();
            if ($hoy->lt($voto->updated_at->addDays(3))) {
                throw new TurnbackException('No puede cambiar su voto tan pronto.');
            }
            $usuario->decrement('puntos', 3);
            $propuesta->decrement($cfgCount[$voto->postura]);
            $sumaAutr -= $cfgPtsAutr[$voto->postura];
            $sumaPost -= $cfgPtsPost[$voto->postura];
        } else if ($voto->publico != $vdt->getData('publico')) {
            $voto->timestamps = false;
            $voto->publico = $vdt->getData('publico');
            $voto->save();
            $this->flash('success', 'La privacidad de su voto fue cambiada exitosamente.');
            $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
        } else {
            throw new TurnbackException('No puede votar dos veces la misma postura.');
        }
        $voto->postura = $postura;
        $voto->publico = $vdt->getData('publico');
        $voto->save();
        $propuesta->increment($cfgCount[$postura]);
        if ($sumaPost != 0) {
            $propuesta->contenido->increment('puntos', $sumaPost);
        }
        if ($sumaAutr != 0) {
            $propuesta->contenido->autor()->increment('puntos', $sumaPost);
        }
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/propuesta/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPropuesta($req->post());
        if ($vdt->getData('referido')) {
            $referido = Contenido::find($vdt->getData('referido'));
            if (is_null($referido) || $referido->contenible_type != 'Problematica') {
                throw new TurnbackException('La problematica asociada no existe.');
            }
        }
        $autor = $this->session->getUser();
        $propuesta = new Propuesta;
        $propuesta->cuerpo = $vdt->getData('cuerpo');
        $propuesta->votos_favor = 0;
        $propuesta->votos_contra = 0;
        $propuesta->votos_neutro = 0;
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->referido_id = $vdt->getData('referido');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        UserlogCtrl::createLog('newPropues', $autor->id, $propuesta);
        $autor->increment('puntos', 25);
        $this->flash('success', 'Su propuesta fue creada exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
    }

    public function verModificar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $propuesta = Propuesta::with('contenido.tags')->findOrFail($idPro);
        $contenido = $propuesta->contenido;
        $datosPropuesta = array_merge($contenido->toArray(), $propuesta->toArray());
        $this->render('contenido/propuesta/modificar.twig', array('propuesta' => $datosPropuesta,
                                                                  'categorias' => $categorias));
    }

    public function modificar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(array('contenido', 'votos'))->findOrFail($idPro);
        $contenido = $propuesta->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarPropuesta($req->post());
        if ($vdt->getData('referido')) {
            $referido = Contenido::find($vdt->getData('referido'));
            if (is_null($referido) || $referido->contenible_type != 'Problematica') {
                throw new TurnbackException('La problematica asociada no existe.');
            }
        }
        $propuesta->cuerpo = $vdt->getData('cuerpo');
        $propuesta->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->referido_id = $vdt->getData('referido');
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        $log = UserlogCtrl::createLog('modPropues', $usuario->id, $propuesta);
        NotificacionCtrl::createNotif($propuesta->votos->lists('usuario_id'), $log);
        $this->flash('success', 'Los datos de la propuesta fueron modificados exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $idPro));
    }

    public function eliminar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(['contenido', 'comentarios.votos'])->findOrFail($idPro);
        $votantes = $propuesta->votos()->lists('usuario_id');
        $propuesta->delete();
        $log = UserlogCtrl::createLog('delPropues', $this->session->user('id'), $propuesta);
        NotificacionCtrl::createNotif($votantes, $log);
        $this->flash('success', 'La propuesta ha sido eliminada exitosamente.');
        $this->redirectTo('shwIndex');
    }

    private function validarPropuesta($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('referido', new Validate\Rule\NumNatural())
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML())
            ->addFilter('referido', FilterFactory::emptyToNull())
            ->addFilter('tags', FilterFactory::explode(','))
            ->addOptional('referido');
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
