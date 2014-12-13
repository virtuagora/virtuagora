<?php use Augusthur\Validation as Validate;

class ComentarioCtrl extends Controller {

    public function comentar($tipoRaiz, $idRaiz) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idRaiz', new Validate\Rule\NumNatural())
            ->addRule('tipoRaiz', new Validate\Rule\InArray(array('Propuesta', 'Problematica', 'Comentario')))
            ->addRule('cuerpo', new Validate\Rule\MinLength(4))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(2048))
            ->addFilter('tipoRaiz', 'ucfirst');
        $req = $this->request;
        $data = array_merge(array('idRaiz' => $idRaiz, 'tipoRaiz' => $tipoRaiz), $req->post());
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $autor = $this->session->getUser();
        $comentable = call_user_func($vdt->getData('tipoRaiz').'::findOrFail', $vdt->getData('idRaiz'));
        if ($vdt->getData('tipoRaiz') == 'Comentario' && isset($comentable->comentable_id)) {
            throw (new TurnbackException())->setErrors(array('No puede responderse una respuesta.'));
        }
        $comentario = new Comentario;
        $comentario->cuerpo = $vdt->getData('cuerpo');
        $comentario->autor()->associate($autor);
        $comentario->comentable()->associate($comentable);
        $comentario->save();
        $this->flash('success', 'Su comentario fue enviado exitosamente.');
        $this->redirect($req->getReferrer());
    }

}
