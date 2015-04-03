<?php use Augusthur\Validation as Validate;

class PropuestaCtrl extends Controller {

    public function ver($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(array('contenido', 'comentarios'))->findOrFail($idPro);
        $contenido = $propuesta->contenido;
        $voto = $propuesta->votos()->where('usuario_id', $this->session->user('id'))->first();
        $comentarios = $propuesta->comentarios->toArray();
        $datosPropuesta = array_merge($contenido->toArray(), $propuesta->toArray());
        $datosVoto = $voto ? $voto->toArray() : null;
        $this->render('contenido/propuesta/ver.twig', array('propuesta' => $datosPropuesta,
                                                            'comentarios' =>  $comentarios,
                                                            'voto' => $datosVoto));
    }

    public function votar($idPro) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray(array(-1, 0, 1)))
            ->addRule('idPro', new Validate\Rule\NumNatural())
            ->addFilter('publico', FilterFactory::radioToBool());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $propuesta = Propuesta::findOrFail($idPro);
        $voto = VotoPropuesta::firstOrNew(array('propuesta_id' => $propuesta->id,
                                                'usuario_id' => $usuario->id));
        $postura = $vdt->getData('postura');
        if (!$voto->exists) {
            $voto->publico = $vdt->getData('publico');
            $usuario->increment('puntos');
            $accion = new Accion;
            $accion->tipo = 'vot_propues';
            $accion->objeto()->associate($propuesta);
            $accion->actor()->associate($usuario);
            $accion->save();
        } else if ($voto->postura == $postura) {
            throw new TurnbackException('No puede votar dos veces la misma postura.');
        } else {
            $fecha = Carbon\Carbon::now();
            $fecha->subDays(3);
            if ($fecha->lt($voto->updated_at)) {
                throw new TurnbackException('No puede cambiar su voto tan pronto.');
            }
            $usuario->decrement('puntos', 5);
            switch ($voto->postura) {
                case -1: $postura->decrement('votos_contra'); break;
                case 0: $postura->decrement('votos_neutro'); break;
                case 1: $postura->decrement('votos_favor'); break;
            }
        }
        $voto->postura = $postura;
        switch ($postura) {
            case -1: $propuesta->increment('votos_contra'); break;
            case 0: $propuesta->increment('votos_neutro'); break;
            case 1: $propuesta->increment('votos_favor'); break;
        }
        $voto->save();
        $usuario->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
    }

    public function cambiarPrivacidad($idPro) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPro', new Validate\Rule\NumNatural())
            ->addRule('publico', new Validate\Rule\InArray(array(1, 0)));
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $voto = VotoPropuesta::where(array('propuesta_id' => $idPro,
                                           'usuario_id' => $this->session->user('id')))->first();
        if (is_null($voto)) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
        }
        $voto->publico = $vdt->getData('publico');
        $voto->save();
        $msg = $voto->publico ? '' : 'no ';
        $this->flash('success', 'Ahora los demás usuarios '.$msg.'podrán ver su postura sobre esta propuesta.');
        $this->redirectTo('shwPropues', array('idPro' => $idPro));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/propuesta/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPropuesta($req->post());
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
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $accion = new Accion;
        $accion->tipo = 'new_propues';
        $accion->objeto()->associate($propuesta);
        $accion->actor()->associate($autor);
        $accion->save();
        $this->flash('success', 'Su propuesta fue creada exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
    }

    public function verModificar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $propuesta = Propuesta::with('contenido')->findOrFail($idPro);
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
        if ($contenido->autor_id != $usuario->id) {
            throw new BearableException('No puede modificar la propuesta de otro.');
        }
        $req = $this->request;
        $vdt = $this->validarPropuesta($req->post());
        $propuesta->cuerpo = $vdt->getData('cuerpo');
        $propuesta->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->save();
        $accion = new Accion;
        $accion->tipo = 'mod_propues';
        $accion->objeto()->associate($propuesta);
        $accion->actor()->associate($usuario);
        $accion->save();
        foreach ($propuesta->votos as $voto) {
            $notif = new Notificacion();
            $notif->usuario_id = $voto->usuario_id;
            $notif->notificable()->associate($accion);
            $notif->save();
        }
        $this->flash('success', 'Los datos de la propuesta fueron modificados exitosamente.');
        $this->redirectTo('shwPropues', array('idPro' => $idPro));
    }

    public function eliminar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(array('contenido', 'comentarios.votos'))->findOrFail($idPro);
        if ($propuesta->contenido->autor_id != $this->session->user('id')) {
            throw new BearableException('No puede eliminar la propuesta de otro.');
        }
        $propuesta->delete();
        $this->flash('success', 'Su propuesta fue eliminada exitosamente.');
        $this->redirect($req->getReferrer());
    }

    private function validarPropuesta($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
