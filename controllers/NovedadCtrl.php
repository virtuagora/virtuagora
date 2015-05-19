<?php use Augusthur\Validation as Validate;

class NovedadCtrl extends Controller {

    public function ver($idNov) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idNov, new Validate\Rule\NumNatural());
        $novedad = Novedad::with(['contenido.tags', 'comentarios'])->findOrFail($idNov);
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
        $novedad->save();
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
        TagCtrl::updateTags($contenido, TagCtrl::getTagIds($vdt->getData('tags')));
        $log = UserlogCtrl::createLog('newNovedad', $autor->id, $novedad);
        if ($contenido->impulsor) {
            NotificacionCtrl::createNotif($partido->afiliados()->lists('id'), $log);
        }
        $this->flash('success', 'Su novedad fue creada exitosamente.');
        $this->redirectTo('shwNovedad', array('idNov' => $novedad->id));
    }

    public function verModificar($idNov) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idNov, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $novedad = Novedad::with('contenido.tags')->findOrFail($idNov);
        $contenido = $novedad->contenido;
        $datos = array_merge($contenido->toArray(), $novedad->toArray());
        $this->render('contenido/novedad/modificar.twig', array('novedad' => $datos,
                                                                'categorias' => $categorias));
    }

    public function modificar($idNov) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idNov, new Validate\Rule\NumNatural());
        $novedad = Novedad::with('contenido')->findOrFail($idNov);
        $contenido = $novedad->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarNovedad($req->post());
        $novedad->cuerpo = $vdt->getData('cuerpo');
        $novedad->save();
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
        $this->flash('success', 'Los datos de la novedad fueron modificados exitosamente.');
        $this->redirectTo('shwNovedad', array('idNov' => $idNov));
    }

    public function eliminar($idNov) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idNov, new Validate\Rule\NumNatural());
        $novedad = Novedad::with(array('contenido', 'comentarios.votos'))->findOrFail($idNov);
        $novedad->delete();
        UserlogCtrl::createLog('delNovedad', $this->session->user('id'), $novedad);
        $this->flash('success', 'La novedad ha sido eliminada exitosamente.');
        $this->redirectTo('shwIndex');
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
            ->addFilter('asociar', FilterFactory::booleanFilter())
            ->addFilter('tags', FilterFactory::explode(','));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
