<?php

class PropuestaCtrl extends Controller {

    public function showPropuesta($id) {
        $validator = new Augusthur\Validation\Validator();
        $validator->add_rule('id', new Augusthur\Validation\Rule\NumNatural());
        if (!$validator->is_valid(array('id' => $id))) {
            $this->notFound();
        }
        $propuesta = Propuesta::findOrFail($id);
        $contenido = $propuesta->contenido;
        $this->render('contenido/propuesta/ver.twig', array('propuesta' => array_merge($contenido->toArray(),
                                                                                      $propuesta->toArray())));
    }

    public function votarPropuesta($idPro) {
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->add_rule('postura', new Augusthur\Validation\Rule\InArray(array(-1, 0, 1)))
            ->add_rule('idPro', new Augusthur\Validation\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$validator->is_valid($data)) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
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

    public function showCrearPropuesta() {
        $this->render('contenido/propuesta/crear.twig');
    }

    public function crearPropuesta() {
        $validator = new Augusthur\Validation\Validator();
        $validator->add_rule('titulo', new Augusthur\Validation\Rule\Alpha(array(' ')));
        $req = $this->request;
        if (!$validator->is_valid($req->post())) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
        }
        $propuesta = new Propuesta;
        $propuesta->cuerpo = $req->post('cuerpo');
        $propuesta->votos_favor = 0;
        $propuesta->votos_contra = 0;
        $propuesta->votos_neutro = 0;
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = $req->post('titulo');
        $contenido->puntos = 0;
        $autor = Usuario::find($this->session->user('id'));
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirect($req->getRootUri().'/propuesta/'.$propuesta->id);
    }

}
