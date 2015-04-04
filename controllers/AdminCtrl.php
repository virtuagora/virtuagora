<?php use Augusthur\Validation as Validate;

class AdminCtrl extends Controller {

    public function verOrganismos() {
        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator(Organismo::query(), $url, $req->get());
        $organismos = $paginator->rows;
        $nav = $paginator->links;
        $this->render('admin/organismos.twig', array('organismos' => $organismos->toArray(),
                                                     'nav' => $nav));
    }

    public function verCrearOrganismo() {
        $this->render('admin/crear-organismo.twig');
    }

    public function crearOrganismo() {
        $req = $this->request;
        $vdt = $this->validarOrganismo($req->post());
        $organismo = new Organismo;
        $organismo->nombre = $vdt->getData('nombre');
        $organismo->descripcion = $vdt->getData('descripcion');
        $organismo->cupo = $vdt->getData('cupo');
        $organismo->save();
        ImageManager::crearImagen('organis', $organismo->id, $organismo->nombre, array(32, 64, 160));
        $this->flash('success', 'Se ha credo el organismo existosamente.');
        $this->redirectTo('shwAdmOrganis');
    }

    public function verModificarOrganismo($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::with('contacto')->findOrFail($idOrg);
        $datosOrganismo = $organismo->toArray();
        $datosOrganismo['contacto'] = $organismo->contacto ? $organismo->contacto->toArray() : null;
        $this->render('admin/mod-organismo.twig', array('organismo' => $datosOrganismo));
    }

    public function modificarOrganismo($idOrg) {
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
        $contacto->save();
        $this->flash('success', 'Los datos del organismo fueron modificados exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function cambiarImgOrganismo($idOrg) {
        ImageManager::cambiarImagen('organis', $idOrg, array(32, 64, 160));
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function eliminarOrganismo($idOrg) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idOrg, new Validate\Rule\NumNatural());
        $organismo = Organismo::findOrFail($idOrg);
        if ($organismo->funcionarios_count > 0) {
            throw new TurnbackException('Para eliminar un organismo no debe haber funcionarios dentro de este.');
        }
        $organismo->delete();
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
            ->addRule('cupo', new Validate\Rule\NumMax(32))
            ->addRule('url', new Validate\Rule\URL())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('telefono', new Validate\Rule\Telephone())
            ->addOptional('url')
            ->addOptional('email')
            ->addOptional('telefono');
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
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
        $usuarios = Usuario::whereIn('id', $entrantes)->whereNull('fecha_validacion')->get();
        if ($usuarios) {
            $usuarios->increment('puntos', 25, array('verified_at' => Carbon\Carbon::now()));
            // TODO definir cuantos puntos se dan
        }
        // TODO crear accion de verificacion de ciudadano
        $this->flash('success', 'Se han verificado los ciudadanos seleccionados exitosamente.');
        $this->redirectTo('shwAdmVrfUsuario');
    }

}
