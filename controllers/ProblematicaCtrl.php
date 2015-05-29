<?php use Augusthur\Validation as Validate;

class ProblematicaCtrl extends Controller {

    public function ver($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $problematica = Problematica::with(['contenido.tags', 'comentarios'])->findOrFail($idPro);
        $contenido = $problematica->contenido;
        $voto = $problematica->votos()->where('usuario_id', $this->session->user('id'))->first();
        $comentarios = $problematica->comentarios->toArray();
        $votos = $problematica->votos()->with('usuario')->get()->toArray();
        $datosProb = array_merge($contenido->toArray(), $problematica->toArray());
        $datosProb['referentes'] = $contenido->referentes->toArray();
        $datosVoto = $voto ? $voto->toArray() : null;
        $this->render('contenido/problematica/ver.twig', ['problematica' => $datosProb,
                                                          'comentarios' => $comentarios,
                                                          'voto' => $datosVoto,
                                                          'votos' => $votos]);
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
        $problemat = Problematica::with('contenido')->findOrFail($idPro);
        $voto = VotoProblematica::firstOrNew(array('problematica_id' => $problemat->id,
                                                   'usuario_id' => $usuario->id));
        $cfgPtsAutr = [0, 1, 2];
        $cfgPtsPost = [1, 2, 3];
        $cfgCount = ['afectados_indiferentes', 'afectados_indirectos', 'afectados_directos'];
        $postura = $vdt->getData('postura');
        $sumaAutr = $cfgPtsAutr[$postura];
        $sumaPost = $cfgPtsPost[$postura];
        if (!$voto->exists) {
            $usuario->increment('puntos', 3);
            UserlogCtrl::createLog('votProblem', $usuario->id, $problemat);
        } else if ($voto->postura == $postura) {
            throw new TurnbackException('No puede votar dos veces la misma postura.');
        } else {
            $fecha = Carbon\Carbon::now();
            $fecha->subDays(3);
            if ($fecha->lt($voto->updated_at)) {
                throw new TurnbackException('No puede cambiar su voto tan pronto.');
            }
            $problemat->decrement($cfgCount[$voto->postura]);
            $sumaAutr -= $cfgPtsAutr[$voto->postura];
            $sumaPost -= $cfgPtsPost[$voto->postura];
        }
        $voto->postura = $postura;
        $voto->save();
        $problemat->increment($cfgCount[$postura]);
        if ($sumaPost != 0) {
            $problemat->contenido->increment('puntos', $sumaPost);
        }
        if ($sumaAutr != 0) {
            $problemat->contenido->autor()->increment('puntos', $sumaPost);
        }
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwProblem', array('idPro' => $problemat->id));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/problematica/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarProblematica($req->post());
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
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        UserlogCtrl::createLog('newProblem', $autor->id, $problematica);
        $autor->increment('puntos', 25);
        $this->flash('success', 'Su problemática se creó exitosamente.');
        $this->redirectTo('shwProblem', array('idPro' => $problematica->id));
    }

    public function verModificar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $problemat = Problematica::with('contenido.tags')->findOrFail($idPro);
        $contenido = $problemat->contenido;
        $datosProp = array_merge($contenido->toArray(), $problemat->toArray());
        $this->render('contenido/problematica/modificar.twig', array('problematica' => $datosProp,
                                                                     'categorias' => $categorias));
    }

    public function modificar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $problemat = Problematica::with(array('contenido', 'votos'))->findOrFail($idPro);
        $contenido = $problemat->contenido;
        $req = $this->request;
        $vdt = $this->validarProblematica($req->post());
        $problemat->cuerpo = $vdt->getData('cuerpo');
        $problemat->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->save();
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        $this->flash('success', 'Los datos de la problemática fueron modificados exitosamente.');
        $this->redirectTo('shwProblem', array('idPro' => $idPro));
    }

    public function eliminar($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $problemat = Problematica::with(['contenido', 'comentarios.votos'])->findOrFail($idPro);
        $votantes = $problemat->votos()->lists('usuario_id');
        $problemat->delete();
        $log = UserlogCtrl::createLog('delProblem', $this->session->user('id'), $problemat);
        NotificacionCtrl::createNotif($votantes, $log);
        $this->flash('success', 'La problematica ha sido eliminada exitosamente.');
        $this->redirectTo('shwIndex');
    }

    private function validarProblematica($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML())
            ->addFilter('tags', FilterFactory::explode(','));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
