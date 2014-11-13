<?php use Augusthur\Validation as Validate;

class ComentarioCtrl extends Controller {

    public function comentar($tipoRaiz, $idRaiz) {
        $safe = new Validate\Validator();
        $safe
            ->addRule('idRaiz', new Validate\Rule\NumNatural())
            ->addRule('tipoRaiz', new Validate\Rule\InArray(array('Propuesta', 'Problematica')))
            ->addRule('cuerpo', new Validate\Rule\MinLength(4))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(2048))
            ->addFilter('cuerpo', 'htmlspecialchars')
            ->addFilter('tipoRaiz', 'ucfirst');
        $req = $this->request;
        $data = array_merge(array('idRaiz' => $idRaiz, 'tipoRaiz' => $tipoRaiz), $req->post());
        if (!$safe->validate($data)) {
            throw (new TurnbackException())->setErrors($safe->getErrors());
        }
        $autor = $this->session->getUser();
        $comentable = call_user_func($safe->getData('tipoRaiz').'::findOrFail', $safe->getData('idRaiz'));
        $comentario = new Comentario;
        $comentario->cuerpo = $safe->getData('cuerpo');
        $comentario->autor()->associate($autor);
        $comentario->comentable()->associate($comentable);
        $comentario->save();
        $this->redirect($req->getReferrer());
    }

}
