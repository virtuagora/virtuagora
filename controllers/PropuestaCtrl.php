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
            ->addRule('idPro', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $propuesta = Propuesta::findOrFail($idPro);
        $voto = VotoPropuesta::firstOrNew(array('propuesta_id' => $propuesta->id,
                                                'usuario_id' => $usuario->id));
        $postura = $vdt->getData('postura');
        if (!$voto->exists) {
            $voto->publico = ($vdt->getData('publico') == 'on');
            $usuario->increment('puntos', 3); // TODO revisar cuantos puntos otorgar
        } else if ($voto->postura == $postura) {
            throw (new TurnbackException())->setErrors(array('No puede votar dos veces la misma postura.'));
        } else {
            $fecha = Carbon\Carbon::now();
            $fecha->subDays(3);
            if ($fecha->lt($voto->updated_at)) {
                throw (new TurnbackException())->setErrors(array('No puede cambiar su voto tan pronto.'));
            }
            $usuario->decrement('puntos', 5); // TODO revisar cuantos puntos quitar
        }
        $voto->postura = $postura;
        switch ($postura) {
            case -1:
                $propuesta->increment('votos_contra');
                break;
            case 0:
                $propuesta->increment('votos_neutro');
                break;
            case 1:
                $propuesta->increment('votos_favor');
                break;
        }
        $voto->save();
        $usuario->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirect($this->urlFor('shwPropues', array('idPro' => $idPro)));
    }

    public function cambiarPrivacidad($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $req = $this->request;
        $voto = VotoPropuesta::where(array('propuesta_id' => $idPro,
                                           'usuario_id' => $this->session->user('id')));
        $voto->publico = ($req->post('publico') == 'on');
        $voto->save();
        $msg = $voto->publico ? '' : 'no ';
        $this->flash('success', 'Ahora los demás usuarios '.$msg.'podrán ver su postura sobre esta propuesta.');
        $this->redirect($this->urlFor('shwPropues', array('idPro' => $idPro)));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/propuesta/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $autor = $this->session->getUser();
        $propuesta = new Propuesta;
        $propuesta->cuerpo = htmlspecialchars($req->post('cuerpo'), ENT_QUOTES);
        $propuesta->votos_favor = 0;
        $propuesta->votos_contra = 0;
        $propuesta->votos_neutro = 0;
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = htmlspecialchars($req->post('titulo'));
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria'); // TODO controlar que existe esa categoria
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirect($this->urlFor('shwPropues', array('idPro' => $propuesta->id)));
    }

}
