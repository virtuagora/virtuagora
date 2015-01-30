<?php use Augusthur\Validation as Validate;

class ComentarioCtrl extends Controller {

    public function comentar($tipoRaiz, $idRaiz) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idRaiz', new Validate\Rule\NumNatural())
            ->addRule('tipoRaiz', new Validate\Rule\InArray(array('Propuesta', 'Problematica', 'Comentario', 'ParrafoDocumento')))
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
        if ($vdt->getData('tipoRaiz') == 'Comentario') {
            if ($comentable->comentable_type == 'Comentario') {
                throw (new TurnbackException())->setErrors(array('No puede responderse una respuesta.'));
            }
            $objType = $comentable->comentable_type;
            $objId = $comentable->comentable_id;
        } else {
            $objType = $vdt->getData('tipoRaiz');
            $objId = $vdt->getData('idRaiz');
        }
        $comentario = new Comentario;
        $comentario->cuerpo = $vdt->getData('cuerpo');
        $comentario->autor()->associate($autor);
        $comentario->comentable()->associate($comentable);
        $comentario->save();
        $accion = new Accion;
        $accion->tipo = 'new_comenta';
        $accion->objeto_type = $objType;
        $accion->objeto_id = $objId;
        $accion->actor()->associate($autor);
        $accion->save();
        $this->flash('success', 'Su comentario fue enviado exitosamente.');
        $this->redirect($req->getReferrer());
    }

    public function votar($idCom) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idCom', new Validate\Rule\NumNatural())
            ->addRule('cantidad', new Validate\Rule\InArray(array(-1, 1)));
        $req = $this->request;
        $data = array_merge(array('idCom' => $idCom), $req->post());
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $comentario = Comentario::findOrFail($idCom);
        $voto = VotoComentario::firstOrNew(array('comentario_id' => $comentario->id,
                                                 'usuario_id' => $usuario->id));
        if (!$voto->exists) {
            $voto->cantidad = $vdt->getData('cantidad');
            $voto->save();
            $comentario->increment('votos', $voto->cantidad);
        } else {
            throw (new TurnbackException())->setErrors(array('No puede votar dos veces el mismo comentario.'));
        }
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirect($req->getReferrer());
    }

}
