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
            ->add_rule('nombre', new Augusthur\Validation\Rule\Alpha(array(' ')))
            ->add_rule('nombre', new Augusthur\Validation\Rule\MinLength(2))
            ->add_rule('nombre', new Augusthur\Validation\Rule\MaxLength(64))
            ->add_rule('descripcion', new Augusthur\Validation\Rule\MaxLength(512))
            ->add_rule('cupo', new Augusthur\Validation\Rule\NumNatural())
            ->add_rule('cupo', new Augusthur\Validation\Rule\NumMin(1))
            ->add_rule('cupo', new Augusthur\Validation\Rule\NumMax(32));
        $req = $this->request;
        if (!$validator->is_valid($req->post())) {
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
            ->add_rule('id', new Augusthur\Validation\Rule\NumNatural())
            ->add_rule('entrantes', new Augusthur\Validation\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'))
            ->add_rule('salientes', new Augusthur\Validation\Rule\Regex('/^\[\d*(?:,\d+)*\]$/'));
        $req = $this->request;
        $data = array_merge(array('id' => $id), $req->post());
        $errormsg = array('Configuración inválida.');
        if (!$validator->is_valid($data)) {
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
