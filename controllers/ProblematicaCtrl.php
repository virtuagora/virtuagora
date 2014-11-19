<?php use Augusthur\Validation as Validate;

class ProblematicaCtrl extends Controller {

    public function showProblematica($idPro) {
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

    public function votarProblematica($idPro) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray(array(0, 1, 2)))
            ->addRule('idPro', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $problematica = Problematica::findOrFail($idPro);
        $voto = VotoProblematica::firstOrNew(array('problematica_id' => $problematica->id,
                                                   'usuario_id' => $usuario->id));
        $postura = $vdt->getData('postura');
        $fecha = Carbon\Carbon::now();
        $fecha->subDays(3);
        if ($fecha->lt($voto->updated_at)) {
            throw (new TurnbackException())->setErrors(array('No puede cambiar su voto tan pronto.'));
        }
        if (!$voto->exists) {
            $voto->usuario_id = $usuario->id;
            $voto->usuario()->associate($usuario);
            $usuario->increment('puntos'); // TODO revisar cuantos puntos otorgar
        } else if ($voto->postura == $postura) {
            throw (new TurnbackException())->setErrors(array('No puede votar dos veces la misma postura.'));
        } else {
            $usuario->decrement('puntos'); // TODO revisar cuantos puntos quitar
        }
        $voto->postura = $postura;
        switch ($postura) {
            case 0:
                $problematica->increment('afectados_indiferentes');
                break;
            case 1:
                $problematica->increment('afectados_indirectos');
                break;
            case 2:
                $problematica->increment('afectados_directos');
                break;
        }
        $voto->save();
        $usuario->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirect($req->getRootUri().'/problematica/'.$idPro);
    }

    public function showCrearProblematica() {
        $categorias = Categoria::all();
        $this->render('contenido/problematica/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crearProblematica() {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addFilter('titulo', 'htmlspecialchars')
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
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
        $contenido->categoria_id = $vdt->getData('categoria'); // TODO controla que existe esa categoria
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($problematica);
        $contenido->save();
        $this->flash('success', 'Su problemática se creó exitosamente.');
        $this->redirect($req->getRootUri().'/problematica/'.$problematica->id);
    }

}
