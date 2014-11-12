<?php

class ComentarioCtrl extends Controller {

    public function comentar($tipoRaiz, $idRaiz) {
        $safe = new Augusthur\Validation\Validator();
        $safe
            ->addRule('idRaiz', new Augusthur\Validation\Rule\NumNatural())
            ->addRule('tipoRaiz', new Augusthur\Validation\Rule\InArray(array('propuesta', 'problematica')));
            ->addFilter('cuerpo', 'htmlspecialchars');
        $req = $this->request;
        $data = array_merge(array('idRaiz' => $idRaiz, 'tipoRaiz' => $tipoRaiz), $req->post());
        if (!$safe->validate($data)) {
            throw (new TurnbackException())->setErrors($validator->get_errors());
        }
        $autor = $this->session->getUser();
        $comentable = $safe->getData('tipoRaiz')::findOrFail($safe->getData('idRaiz'));
        $comentario = new Comentario;
        $comentario->cuerpo = $safe->getData('cuerpo');
        $comentario->autor()->associate($autor);
        $comentario->comentable()->associate($comentable);
        $this->redirect($req->getReferrer());
    }

}
