<?php

class ProblematicaCtrl extends Controller {

    public function showProblematica($id) {
        $validator = new Augusthur\Validation\Validator();
        $validator->addRule('id', new Augusthur\Validation\Rule\NumNatural());
        if (!$validator->validate(array('id' => $id))) {
            $this->notFound();
        }
        $problematica = Problematica::findOrFail($id);
        $contenido = $problematica->contenido;
        $datosProblematica = array_merge($contenido->toArray(), $problematica->toArray());
        $this->render('contenido/problematica/ver.twig', array('problematica' => $datosProblematica));
    }

    public function showCrearProblematica() {
        $this->render('contenido/problematica/crear.twig');
    }

    public function crearProblematica() {
        $validator = new Augusthur\Validation\Validator();
        $validator
            ->addRule('titulo', new Augusthur\Validation\Rule\MinLength(8))
            ->addRule('titulo', new Augusthur\Validation\Rule\MaxLength(128));
        $req = $this->request;
        if (!$validator->validate($req->post())) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
        }
        $autor = $this->session->getUser();
        $problematica = new Problematica;
        $problematica->cuerpo = htmlspecialchars($req->post('cuerpo'), ENT_QUOTES);
        $problematica->afectados_directos = 0;
        $problematica->afectados_indirectos = 0;
        $problematica->afectados_indiferenctes = 0;
        $problematica->save();
        $contenido = new Contenido;
        $contenido->titulo = htmlspecialchars($req->post('titulo'));
        $contenido->puntos = 0;
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirect($req->getRootUri().'/problematica/'.$propuesta->id);
    }

}
