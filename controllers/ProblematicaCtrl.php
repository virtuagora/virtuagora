<?php use Augusthur\Validation as Validate;

class ProblematicaCtrl extends Controller {

    public function ver($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $problematica = Problematica::with(array('contenido', 'comentarios'))->findOrFail($idPro);
        $contenido = $problematica->contenido;
        $voto = $problematica->votos()->where('usuario_id', $this->session->user('id'))->first();
        $comentarios = $problematica->comentarios->toArray();
        $datosProblematica = array_merge($contenido->toArray(), $problematica->toArray());
        $datosVoto = $voto ? $voto->toArray() : null;
        $this->render('contenido/problematica/ver.twig', array('problematica' => $datosProblematica,
                                                               'comentarios' => $comentarios,
                                                               'voto' => $datosVoto));
    }

    public function votar($idPro) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray(array(0, 1, 2)))
            ->addRule('idPro', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $problematica = Problematica::findOrFail($idPro);
        $voto = VotoProblematica::firstOrNew(array('problematica_id' => $problematica->id,
                                                   'usuario_id' => $usuario->id));
        $postura = $vdt->getData('postura');
        if (!$voto->exists) {
            $usuario->increment('puntos');
            $accion = new Accion;
            $accion->tipo = 'vot_problem';
            $accion->objeto()->associate($problematica);
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
                case 0: $problematica->decrement('afectados_indiferentes'); break;
                case 1: $problematica->decrement('afectados_indirectos'); break;
                case 2: $problematica->decrement('afectados_directos'); break;
            }
        }
        $voto->postura = $postura;
        switch ($postura) {
            case 0: $problematica->increment('afectados_indiferentes'); break;
            case 1: $problematica->increment('afectados_indirectos'); break;
            case 2: $problematica->increment('afectados_directos'); break;
        }
        $voto->save();
        $usuario->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwProblem', array('idPro' => $problematica->id));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/problematica/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $autor = $this->session->getUser();
        $problematica = new Problematica;
        $problematica->cuerpo = $vdt->getData('cuerpo');
        $problematica->afectados_directos = 0;
        $problematica->afectados_indirectos = 0;
        $problematica->afectados_indiferentes = 0;
        $problematica->save();
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($problematica);
        $contenido->save();
        $accion = new Accion;
        $accion->tipo = 'new_problem';
        $accion->objeto()->associate($problematica);
        $accion->actor()->associate($autor);
        $accion->save();
        $this->flash('success', 'Su problemática se creó exitosamente.');
        $this->redirectTo('shwProblem', array('idPro' => $problematica->id));
    }

}
