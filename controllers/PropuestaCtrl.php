<?php

class PropuestaCtrl extends Controller {

    public function showPropuesta($id) {
        $validator = new Augusthur\Validation\Validator();
        $validator->addRule('id', new Augusthur\Validation\Rule\NumNatural());
        if (!$validator->validate(array('id' => $id))) {
            $this->notFound();
        }
        $propuesta = Propuesta::with(array('contenido', 'comentarios'))->findOrFail($id);
        $contenido = $propuesta->contenido;
        $comentarios = $propuesta->comentarios;
        $datosPropuesta = array_merge($contenido->toArray(), $propuesta->toArray());
        $this->render('contenido/propuesta/ver.twig', array('propuesta' => $datosPropuesta,
                                                            'comentarios' =>  $comentarios->toArray()));
    }

    public function votarPropuesta($idPro) {
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('postura', new Augusthur\Validation\Rule\InArray(array(-1, 0, 1)))
            ->addRule('idPro', new Augusthur\Validation\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(array('idPro' => $idPro), $req->post());
        if (!$validator->validate($data)) {
            throw (new TurnbackException())->setErrors($validator->getErrors());
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
        $validator
            ->addRule('titulo', new Augusthur\Validation\Rule\MinLength(8))
            ->addRule('titulo', new Augusthur\Validation\Rule\MaxLength(128));
        $req = $this->request;
        if (!$validator->validate($req->post())) {
            throw (new TurnbackException())->setErrors($validator->getErrors());
        }
        $autor = $this->session->getUser();
        $propuesta = new Propuesta;
        $propuesta->cuerpo = htmlspecialchars($req->post('cuerpo'), ENT_QUOTES);
        $propuesta->votos_favor = 0;
        $propuesta->votos_contra = 0;
        $propuesta->votos_neutro = 0;
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = htmlspecialchars($req->post('titulo'));
        $contenido->puntos = 0;
        $contenido->categoria_id = 1; ////////////////////////////////////////////////////////////////////////////
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirect($req->getRootUri().'/propuestas/'.$propuesta->id);
    }

}
