<?php use Augusthur\Validation as Validate;

class OrganismoCtrl extends RMRController {

    protected $mediaTypes = ['json', 'view'];
    protected $properties = ['id', 'nombre', 'cupo', 'funcionarios_count'];
    protected $searchable = true;

    public function queryModel($meth, $repr) {
        return Organismo::query();
    }

    public function executeListCtrl($paginator) {
        $organismos = $paginator->rows->toArray();
        $nav = $paginator->links;
        $this->render('organismo/listar.twig', array('organismos' => $organismos,
                                                     'nav' => $nav));
    }

    public function executeGetCtrl($organismo) {
        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($organismo->usuarios(), $url, $req->get());
        $funcionarios = $paginator->rows->toArray();
        $nav = $paginator->links;
        $this->render('organismo/ver.twig', array('organismo' => $organismo->toArray(),
                                                  'funcionarios' => $funcionarios,
                                                  'nav' => $nav));
    }

    public function listarInterno() {
        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator(Organismo::query(), $url, $req->get());
        $organismos = $paginator->rows;
        $nav = $paginator->links;
        $this->render('admin/organismos.twig', array('organismos' => $organismos->toArray(),
                                                     'nav' => $nav));
    }

    public function verCrear() {
        $this->render('admin/crear-organismo.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarOrganismo($req->post());
        $organismo = new Organismo;
        $organismo->nombre = $vdt->getData('nombre');
        $organismo->descripcion = $vdt->getData('descripcion');
        $organismo->cupo = $vdt->getData('cupo');
        $organismo->save();
        ImageManager::crearImagen('organis', $organismo->id, $organismo->nombre, array(32, 64, 160));
        AdminlogCtrl::createLog('', 3, 'new', $this->session->user('id'), $organismo);
        $this->flash('success', 'Se ha credo el organismo existosamente.');
        $this->redirectTo('shwAdmOrganis');
    }

    public function verModificar($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::with('contacto')->findOrFail($idOrg);
        $datosOrganismo = $organismo->toArray();
        $datosOrganismo['contacto'] = $organismo->contacto ? $organismo->contacto->toArray() : null;
        $this->render('admin/mod-organismo.twig', array('organismo' => $datosOrganismo));
    }

    public function modificar($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::with('contacto')->findOrFail($idOrg);
        $req = $this->request;
        $vdt = $this->validarOrganismo($req->post());
        $organismo->nombre = $vdt->getData('nombre');
        $organismo->descripcion = $vdt->getData('descripcion');
        if ($vdt->getData('cupo') < $organismo->funcionarios_count) {
            throw new TurnbackException('Actualmente hay más funcionarios que el cupo deseado, elimine algunos.');
        } else {
            $organismo->cupo = $vdt->getData('cupo');
        }
        $organismo->save();
        $contacto = $organismo->contacto ?: new Contacto;
        $contacto->email = $vdt->getData('email');
        $contacto->web = $vdt->getData('url');
        $contacto->telefono = $vdt->getData('telefono');
        $contacto->contactable()->associate($organismo);
        $contacto->save();
        AdminlogCtrl::createLog('', 3, 'mod', $this->session->user('id'), $organismo);
        $this->flash('success', 'Los datos del organismo fueron modificados exitosamente.');
        $this->redirectTo('shwAdmOrganis');
    }

    public function cambiarImagen($idOrg) {
        ImageManager::cambiarImagen('organis', $idOrg, array(32, 64, 160));
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function eliminar($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::findOrFail($idOrg);
        if ($organismo->funcionarios_count > 0) {
            echo 'lala1';
            throw new TurnbackException('Para eliminar un organismo no debe haber funcionarios dentro de este.');
        }
        $organismo->delete();
        AdminlogCtrl::createLog('', 3, 'del', $this->session->user('id'), $organismo);
        $this->flash('success', 'El organismo fue eliminado exitosamente.');
        $this->redirectTo('shwAdmOrganis');
    }

    private function validarOrganismo($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512))
            ->addRule('cupo', new Validate\Rule\NumNatural())
            ->addRule('cupo', new Validate\Rule\NumMin(1))
            ->addRule('cupo', new Validate\Rule\NumMax(128))
            ->addRule('url', new Validate\Rule\URL())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('telefono', new Validate\Rule\Telephone())
            ->addOptional('url')
            ->addOptional('email')
            ->addOptional('telefono')
            ->addFilter('url', FilterFactory::emptyToNull())
            ->addFilter('email', FilterFactory::emptyToNull())
            ->addFilter('telefono', FilterFactory::emptyToNull());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
