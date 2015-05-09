<?php use Augusthur\Validation as Validate;

class PatrullaCtrl extends RMRController {

    protected $mediaTypes = array('json', 'view');
    protected $properties = array('id', 'nombre', 'descripcion');

    public function queryModel($meth, $repr) {
        switch ($meth) {
            case 0: return Patrulla::query();
            case 1: return Patrulla::with('moderadores');
        }
    }

    public function executeListCtrl($paginator) {
        $patrullas = $paginator->rows;
        $nav = $paginator->links;
        $this->render('admin/patrullas.twig', array('patrullas' => $patrullas->toArray(),
                                                    'nav' => $nav));
    }

    public function executeGetCtrl($patrulla) {
        $this->render('admin/moderadores.twig', array('patrulla' => $patrulla->toArray(),
                                                      'moderadores' => $patrulla->moderadores->toArray()));
    }

    public function verCrearModeradores() {
        $patrullas = Patrulla::all();
        $this->render('admin/crear-moderadores.twig', array('patrullas' => $patrullas->toArray()));
    }

    public function crearModeradores() {
        $vdt = new Validate\Validator();
        $vdt->addRule('entrantes', new Validate\Rule\Attributes(['usr' => 'ctype_digit', 'pat' => 'ctype_digit']))
            ->addFilter('entrantes', FilterFactory::json_decode());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        foreach ($vdt->getData('entrantes') as $entrante) {
            $usuario = Usuario::findOrFail($entrante['usr']);
            $patrulla = Patrulla::findOrFail($entrante['pat']);
            $usuario->patrulla()->associate($patrulla);
            $usuario->save();
            $identidad = $usuario->identidad.' ('.$usuario->id.')';
            $log = AdminlogCtrl::createLog($identidad, 6, 'new', $this->session->user('id'), $patrulla);
            NotificacionCtrl::createNotif($usuario->id, $log);
        }
        $this->flash('success', 'Los nuevos moderadores han sido agregados exitosamente.');
        $this->redirectTo('shwCrearModerad');
    }

    public function adminModeradores($idPat) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPat', new Validate\Rule\NumNatural())
            ->addRule('salientes', new Validate\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'));
        $req = $this->request;
        $data = array_merge(array('idPat' => $idPat), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $patrulla = Patrulla::findOrFail($idPat);
        $salientes = json_decode($vdt->getData('salientes'));
        if (in_array($this->session->user('id'), $salientes)) {
            throw new TurnbackException('No puede quitarse a sí mismo de una patrulla.');
        }
        $patrulla->moderadores()->whereIn('id', $salientes)->update(['patrulla_id' => null]);
        $log = AdminlogCtrl::createLog(implode(',', $salientes), 6, 'del', $this->session->user('id'), $patrulla);
        NotificacionCtrl::createNotif($salientes, $log);
        $this->flash('success', 'Los moderadores han sido removidos de la patrulla exitosamente.');
        $this->redirectTo('shwAdmModerad', ['idPat' => $idPat]);
    }

    public function verCrear() {
        $this->render('admin/crear-patrulla.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPatrulla($req->post());
        $patrulla = new Patrulla;
        $patrulla->nombre = $vdt->getData('nombre');
        $patrulla->descripcion = $vdt->getData('descripcion');
        $patrulla->save();
        AdminlogCtrl::createLog('', 5, 'new', $this->session->user('id'), $patrulla);
        $this->flash('success', 'El grupo de moderación ha sido creado exitosamente.');
        $this->redirectTo('shwAdmPatrull');
    }

    public function modificar($idPat) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPat, new Validate\Rule\NumNatural());
        $patrulla = Patrulla::findOrFail($idPat);
        $req = $this->request;
        $vdt = $this->validarPatrulla($req->post());
        $patrulla->nombre = $vdt->getData('nombre');
        $patrulla->descripcion = $vdt->getData('descripcion');
        $patrulla->save();
        AdminlogCtrl::createLog('', 5, 'mod', $this->session->user('id'), $patrulla);
        $this->flash('success', 'Los datos del grupo de moderación fueron modificados exitosamente.');
        $this->redirectTo('shwAdmPatrull');
    }

    public function eliminar($idPat) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPat, new Validate\Rule\NumNatural());
        $patrulla = Patrulla::with('moderadores')->findOrFail($idPat);
        if (!$patrulla->moderadores->isEmpty()) {
            throw new TurnbackException('Para eliminar una patrulla no debe haber moderadores dentro de esta.');
        }
        $patrulla->delete();
        AdminlogCtrl::createLog('', 5, 'del', $this->session->user('id'), $patrulla);
        $this->flash('success', 'La patrulla ha sido eliminada exitosamente.');
        $this->redirectTo('shwAdmPatrull');
    }

    public function verCambiarPoder($idPat) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPat, new Validate\Rule\NumNatural());
        $patrulla = Patrulla::findOrFail($idPat);
        $datosPat = $patrulla->toArray();
        $datosPat['poderes'] = $patrulla->poderes()->lists('poder_id');
        $poderes = Poder::all()->toArray();
        $this->render('admin/gestionar-poderes.twig', array('patrulla' => $datosPat,
                                                            'poderes' => $poderes));
    }

    public function cambiarPoder($idPat) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPat', new Validate\Rule\NumNatural())
            ->addRule('poderes', new Validate\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'));
        $req = $this->request;
        $data = array_merge(array('idPat' => $idPat), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $patrulla = Patrulla::findOrFail($idPat);
        $poderes = json_decode($vdt->getData('poderes'));
        $patrulla->poderes()->sync($poderes);
        AdminlogCtrl::createLog(implode(',', $poderes), 5, 'pod', $this->session->user('id'), $patrulla);
        $this->flash('success', 'Los permisos del grupo de moderación fueron modificados exitosamente.');
        $this->redirectTo('shwAdmPatrull');
    }

    private function validarPatrulla($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }

}
