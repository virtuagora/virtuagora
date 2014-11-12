<?php

class AdminCtrl extends Controller {

    public function showOrganismos() {
        $organismos = Organismo::all();
        $this->render('admin/organismos.twig', array('organismos' => $organismos->toArray()));
    }

    public function showCrearOrganismo() {
        $this->render('admin/crear-organismo.twig');
    }

    public function crearOrganismo() {
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('nombre', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Augusthur\Validation\Rule\MinLength(2))
            ->addRule('nombre', new Augusthur\Validation\Rule\MaxLength(64))
            ->addRule('descripcion', new Augusthur\Validation\Rule\MaxLength(512))
            ->addRule('cupo', new Augusthur\Validation\Rule\NumNatural())
            ->addRule('cupo', new Augusthur\Validation\Rule\NumMin(1))
            ->addRule('cupo', new Augusthur\Validation\Rule\NumMax(32));
        $req = $this->request;
        if (!$validator->validate($req->post())) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
        }
        $organismo = new Organismo;
        $organismo->nombre = $req->post('nombre');
        $organismo->descripcion = $req->post('descripcion');
        $organismo->cupo = $req->post('cupo');
        $organismo->imagen = false;
        $organismo->save();
        $this->redirect($req->getRootUri().'/admin/organismos');
    }

    public function showAdminFuncionarios($id) {
        $organismo = Organismo::findOrFail($id);
        $this->render('admin/funcionarios.twig', array('organismo' => $organismo->toArray(),
                                                       'funcionarios' => $organismo->usuarios->toArray()));
    }

    public function adminFuncionarios($id) {
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('id', new Augusthur\Validation\Rule\NumNatural())
            ->addRule('entrantes', new Augusthur\Validation\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'))
            ->addRule('salientes', new Augusthur\Validation\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'));
        $req = $this->request;
        $data = array_merge(array('id' => $id), $req->post());
        $errormsg = array('Configuración inválida.');
        if (!$validator->validate($data)) {
            throw (new TurnbackException())->setErrors($errormsg);
        }
        $organismo = Organismo::findOrFail($id);
        $funcionarios = $organismo->funcionarios;
        $actuales = array();
        foreach ($funcionarios as $funcionario) {
            $actuales[] = (int) $funcionario->usuario_id;
        }
        $entrantes = json_decode($req->post('entrantes'));
        $salientes = json_decode($req->post('salientes'));
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
        $this->redirect($req->getRootUri().'/admin/organismos');
    }

}
