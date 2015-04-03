<?php use Augusthur\Validation as Validate;

class NovedadCtrl extends Controller {

    public function ver($idNov) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idNov, new Validate\Rule\NumNatural());
        $novedad = Novedad::with(array('contenido', 'comentarios'))->findOrFail($idNov);
        $contenido = $novedad->contenido;
        $comentarios = $novedad->comentarios->toArray();
        $datos = array_merge($contenido->toArray(), $novedad->toArray());
        $this->render('contenido/novedad/ver.twig', array('novedad' => $datos,
                                                          'comentarios' =>  $comentarios));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/novedad/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarNovedad($req->post());
        $autor = $this->session->getUser();
        $novedad = new Novedad;
        $novedad->cuerpo = $vdt->getData('cuerpo');
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($novedad);
        $partido = $autor->partido;
        if (isset($partido) && $vdt->getData('asociar')) {
            $contenido->impulsor()->associate($partido);
        }
        $contenido->save();
        $accion = new Accion;
        $accion->tipo = 'new_novedad';
        $accion->objeto()->associate($novedad);
        $accion->actor()->associate($autor);
        $accion->save();
        $this->flash('success', 'Su novedad fue creada exitosamente.');
        $this->redirectTo('shwNovedad', array('idNov' => $novedad->id));
    }

    private function validarNovedad($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML())
            ->addFilter('asociar', FilterFactory::booleanFilter());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
