<?php use Augusthur\Validation as Validate;

class ComentarioCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'comentable_type', 'comentable_id', 'votos', 'created_at', 'updated_at', 'autor_id'];

    public function queryModel($meth, $repr) {
        return Comentario::query();
    }

    public function comentar($tipoRaiz, $idRaiz) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idRaiz', new Validate\Rule\NumNatural())
            ->addRule('tipoRaiz', new Validate\Rule\InArray(array('Propuesta', 'Problematica', 'Comentario', 'ParrafoDocumento', 'Novedad')))
            ->addRule('cuerpo', new Validate\Rule\MinLength(4))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(2048))
            ->addFilter('tipoRaiz', 'ucfirst');
        $req = $this->request;
        $data = array_merge(array('idRaiz' => $idRaiz, 'tipoRaiz' => $tipoRaiz), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $autor = $this->session->getUser();
        $comentable = call_user_func($vdt->getData('tipoRaiz').'::findOrFail', $vdt->getData('idRaiz'));
        if ($vdt->getData('tipoRaiz') == 'Comentario') {
            if ($comentable->comentable_type == 'Comentario') {
                throw new TurnbackException('No puede responderse una respuesta.');
            } else if ($comentable->comentable_type == 'ParrafoDocumento') {
                $objType = 'Parrafo';
                $objId = $comentable->version->documento_id;
            }
            $objType = $comentable->comentable_type;
            $objId = $comentable->comentable_id;
        } else if ($vdt->getData('tipoRaiz') == 'ParrafoDocumento') {
            $objType = 'Parrafo';
            $objId = $comentable->version->documento_id;
        } else {
            $objType = $vdt->getData('tipoRaiz');
            $objId = $vdt->getData('idRaiz');
        }
        $comentario = new Comentario;
        $comentario->cuerpo = $vdt->getData('cuerpo');
        $comentario->autor()->associate($autor);
        $comentario->comentable()->associate($comentable);
        $comentario->save();
        UserlogCtrl::createLog('newComenta', $autor, $objId, $objType);
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
            throw new TurnbackException($vdt->getErrors());
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
            throw new TurnbackException('No puede votar dos veces el mismo comentario.');
        }
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirect($req->getReferrer());
    }

}
