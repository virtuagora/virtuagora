<?php use Augusthur\Validation as Validate;

class ProblematicaCtrl extends Controller {

    public function showProblematica($id) {
        $vdt = new Validate\Validator();
        $vdt->addRule('id', new Validate\Rule\NumNatural());
        if (!$vdt->quickValidate(array('id' => $id))) {
            $this->notFound();
        }
        $problematica = Problematica::with('contenido')->findOrFail($id);
        $contenido = $problematica->contenido;
        $voto = $problematica->votos()->where('usuario_id', $this->session->user('id'))->first();
        $datosProblematica = array_merge($contenido->toArray(), $problematica->toArray());
        $this->render('contenido/problematica/ver.twig', array('problematica' => $datosProblematica,
                                                               'voto' => $voto->toArray()));
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
        // TODO validar que no se vota mas de una vez cada X tiempo.
        if (!$voto->exists) {
            $voto->usuario_id = $usuario->id;
            $voto->problematica_id = $problematica->id;
            $usuario->increment('puntos'); // TODO revisar cuantos puntos otorgar
        } else if ($voto->postura == $postura) {
            throw new BearableException('No puede votar dos veces la misma postura.');
        } else {
            $usuario->decrement('puntos'); // TODO revisar cuantos puntos quitar
        }
        $voto->postura = $postura;
        switch ($postura) {
            case 0:
                $propuesta->increment('afectados_directos');
                break;
            case 1:
                $propuesta->increment('afectados_indirectos');
                break;
            case 2:
                $propuesta->increment('afectados_indiferentes');
                break;
        }
        $voto->save();
        $usuario->save();
        //$problematica->save();
        $this->redirect($req->getRootUri().'/problematica/'.$idPro);
    }

    public function showCrearProblematica() {
        $this->render('contenido/problematica/crear.twig');
    }

    public function crearProblematica() {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('titulo', 'htmlspecialchars')
            ->addFilter('cuerpo', function ($v) {return htmlspecialchars($v, ENT_QUOTES);});
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
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirect($req->getRootUri().'/problematica/'.$propuesta->id);
    }

}
