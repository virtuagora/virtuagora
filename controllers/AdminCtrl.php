<?php use Augusthur\Validation as Validate;

class AdminCtrl extends Controller {

    public function verAdminAjustes() {
        $ajustes = Ajuste::all();
        $this->render('admin/ajustes.twig', array('ajustes' => $ajustes->toArray()));
    }

    public function adminAjustes() {
        $vdt = new Validate\Validator();
        $vdt->addRule('tos', new Validate\Rule\MinLength(8))
            ->addRule('tos', new Validate\Rule\MaxLength(8192))
            ->addFilter('tos', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $ajustes = Ajuste::all();
        foreach ($ajustes as $ajuste) {
            $newValue = $vdt->getData($ajuste->key);
            if (isset($newValue)) {
                $ajuste->value = $newValue;
                $ajuste->save();
            }
        }
        $this->flash('success', 'Los ajustes se han modificado exitosamente.');
        $this->redirectTo('shwAdmAjuste');
    }

    public function verAdminFuncionarios($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::findOrFail($idOrg);
        $this->render('admin/funcionarios.twig', array('organismo' => $organismo->toArray(),
                                                       'funcionarios' => $organismo->usuarios->toArray()));
    }

    public function adminFuncionarios($id) {
        $vdt = new Validate\Validator();
        $vdt->addRule('id', new Validate\Rule\NumNatural())
            ->addRule('entrantes', new Validate\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'))
            ->addRule('salientes', new Validate\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'));
        $req = $this->request;
        $data = array_merge(array('id' => $id), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException('Configuración inválida.');
        }
        $organismo = Organismo::findOrFail($id);
        $funcionarios = $organismo->funcionarios;
        $actuales = array();
        foreach ($funcionarios as $funcionario) {
            $actuales[] = (int) $funcionario->usuario_id;
        }
        $entrantes = json_decode($vdt->getData('entrantes'));
        $salientes = json_decode($vdt->getData('salientes'));
        if (array_intersect($actuales, $entrantes)) {
            throw new TurnbackException('Configuración inválida.');
        }
        if (array_diff($salientes, $actuales)) {
            throw new TurnbackException('Configuración inválida.');
        }
        if ($salientes) {
            Funcionario::whereIn('usuario_id', $salientes)->delete();
            Usuario::whereIn('id', $salientes)->update(array('es_funcionario' => false));
        }
        foreach ($entrantes as $entrante) {
            $funcionario = new Funcionario;
            $funcionario->usuario_id = $entrante;
            $funcionario->organismo_id = $id;
            $funcionario->save();
        }
        $this->flash('success', 'Se han modificado los funcionarios del organismo exitosamente.');
        $this->redirectTo('shwAdmOrganis');
    }

    public function sancUsuario($idUsr) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idUsr', new Validate\Rule\NumNatural())
            ->addRule('tipo', new Validate\Rule\InArray(array('Suspension', 'Advertencia', 'Quita')))
            ->addRule('mensaje', new Validate\Rule\MinLength(4))
            ->addRule('mensaje', new Validate\Rule\MaxLength(128))
            ->addRule('cantidad', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idUsr' => $idUsr), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::findOrFail($vdt->getData('idUsr'));
        switch ($vdt->getData('tipo')) {
            case 'Suspension':
                $usuario->suspendido = true;
                if ($vdt->getData('cantidad') > 0) {
                    $usuario->fin_suspension = Carbon\Carbon::now()->addDays($vdt->getData('cantidad'));
                } else {
                    $usuario->fin_suspension = null;
                }
                $usuario->save();
                $mensaje = "El usuario fue suspendido.";
                break;
            case 'Advertencia':
                $usuario->advertencia = $vdt->getData('mensaje');
                $usuario->fin_advertencia = Carbon\Carbon::now()->addDays($vdt->getData('cantidad'));
                $usuario->save();
                $mensaje = "El usuario ha sido advertido.";
                break;
            case 'Quita':
                $usuario->decrement('puntos', $vdt->getData('cantidad'));
                $mensaje = "Se le han quitado los puntos al usuario.";
                break;
        }
        $this->flash('success', $mensaje);
        $this->redirect($req->getReferrer());
    }

    public function verVerifCiudadano() {
        $this->render('admin/verificar-usuarios.twig');
    }

    public function verifCiudadano() {
        $vdt = new Validate\Validator();
        $vdt->addRule('entrantes', new Validate\Rule\Regex('/^\[\d+(?:,\d+)*\]$/'));
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException('Configuración inválida.');
        }
        $entrantes = json_decode($vdt->getData('entrantes'));
        Usuario::whereIn('id', $entrantes)->whereNull('verified_at')
            ->increment('puntos', 25, array('verified_at' => Carbon\Carbon::now()));
        // TODO definir cuantos puntos se dan
        // TODO crear accion de verificacion de ciudadano
        $this->flash('success', 'Se han verificado los ciudadanos seleccionados exitosamente.');
        $this->redirectTo('shwAdmVrfUsuario');
    }

}
