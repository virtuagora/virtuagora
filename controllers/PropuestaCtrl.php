<?php use Augusthur\Validation as Validate;

class PropuestaCtrl extends Controller {

    public function ver($idPro) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPro, new Validate\Rule\NumNatural());
        $propuesta = Propuesta::with(array('contenido', 'comentarios'))->findOrFail($idPro);
        $contenido = $propuesta->contenido;
        $comentarios = $propuesta->comentarios;
        $datosPropuesta = array_merge($contenido->toArray(), $propuesta->toArray());
        $this->render('contenido/propuesta/ver.twig', array('propuesta' => $datosPropuesta,
                                                            'comentarios' =>  $comentarios->toArray()));
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
        $idUsuario = $this->session->user('id');
        $publico = false;
        if ($req->post('publico') == 'on') {
            $publico = true;
        }
        $postura = $req->post('postura');
        $propuesta = Propuesta::findOrFail($idPro);
        $propuesta->posturas()->attach($idUsuario, array('postura' => $postura,
                                                         'publico' => $publico));
        switch ($postura) {
            case -1:
                $propuesta->votos_contra++;
                break;
            case 0:
                $propuesta->votos_neutro++;
                break;
            case 1:
                $propuesta->votos_favor++;
                break;
        }
        $propuesta->save();
        $this->redirect($req->getRootUri().'/propuesta/'.$propuesta->id);
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
        $this->redirect($req->getRootUri().'/propuesta/'.$propuesta->id);
    }

}
