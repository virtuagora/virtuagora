<?php use Augusthur\Validation as Validate;

class AdminCtrl extends Controller {

    public function verOrganismos() {
        $organismos = Organismo::all();
        $this->render('admin/organismos.twig', array('organismos' => $organismos->toArray()));
    }

    public function verCrearOrganismo() {
        $this->render('admin/crear-organismo.twig');
    }

    public function crearOrganismo() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512))
            ->addRule('cupo', new Validate\Rule\NumNatural())
            ->addRule('cupo', new Validate\Rule\NumMin(1))
            ->addRule('cupo', new Validate\Rule\NumMax(32));
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $organismo = new Organismo;
        $organismo->nombre = $vdt->getData('nombre');
        $organismo->descripcion = $vdt->getData('descripcion');
        $organismo->cupo = $vdt->getData('cupo');
        $organismo->imagen = false;
        $organismo->save();
        $this->redirect($req->getRootUri().'/admin/organismo');
    }

    public function verAdminFuncionarios($id) {
        $organismo = Organismo::findOrFail($id);
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
        $errormsg = array('Configuración inválida.');
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($errormsg);
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
            throw (new TurnbackException())->setErrors($errormsg);
        }
        if (array_diff($salientes, $actuales)) {
            throw (new TurnbackException())->setErrors($errormsg);
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
        $this->redirect($req->getRootUri().'/admin/organismo');
    }

}
