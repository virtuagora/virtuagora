<?php use Augusthur\Validation as Validate;

class DocumentoCtrl extends Controller {

    public function ver($idDoc, $idVer = 0) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idDoc, new Validate\Rule\NumNatural());
        $vdt->test($idVer, new Validate\Rule\NumNatural());
        $documento = Documento::with('contenido')->findOrFail($idDoc);
        $contenido = $documento->contenido;
        if ($idVer == 0) {
            $idVer = $documento->ultima_version;
        }
        $version = $documento->versiones()->where('version', $idVer)->first();
        $datosDocumento = array_merge($contenido->toArray(), $documento->toArray());
        $this->render('contenido/documento/ver.twig', array('documento' => $datosDocumento,
                                                            'version' =>  $version->toArray()));
    }

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/documento/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDocumento($req->post(), true);
        $autor = $this->session->getUser();
        $documento = new Documento;
        $documento->descripcion = $vdt->getData('descripcion');
        $documento->ultima_version = 1;
        $documento->save();
        $docVersion = new VersionDocumento;
        $docVersion->version = 1;
        $docVersion->documento()->associate($documento);
        $docVersion->save();
        $parrafos = $this->parsearParrafos($vdt->getData('cuerpo'));
        foreach ($parrafos as $i => $parrafo) {
            $docParrafo = new ParrafoDocumento;
            $docParrafo->cuerpo = htmlspecialchars($parrafo, ENT_QUOTES);
            $docParrafo->ubicacion = $i;
            $docParrafo->version()->associate($docVersion);
            $docParrafo->save();
        }
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($documento);
        $contenido->save();
        $accion = new Accion;
        $accion->tipo = 'new_documen';
        $accion->objeto()->associate($documento);
        $accion->actor()->associate($autor);
        $accion->save();
        $this->flash('success', 'Su documento abierto se creó exitosamente.');
        $this->redirectTo('shwDocumen', array('idDoc' => $documento->id));
    }

    public function verNuevaVersion($idDoc) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idDoc, new Validate\Rule\NumNatural());
        $documento = Documento::with('contenido')->findOrFail($idDoc);
        $contenido = $documento->contenido;
        $version = $documento->versiones()->where('version', $documento->ultima_version)->first();
        $datosDocumento = array_merge($contenido->toArray(), $documento->toArray());
        $this->render('contenido/documento/nueva-version.twig', array('documento' => $datosDocumento,
                                                                      'version' =>  $version->toArray()));
    }

    public function nuevaVersion($idDoc) {
        $vdt = new Validate\Validator();
        $vdt->addRule($idDoc, new Validate\Rule\NumNatural())
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $documento = Documento::findOrFail($idDoc);
        $documento->increment('ultima_version');
        $docVersion = new VersionDocumento;
        $docVersion->version = $documento->ultima_version;
        $docVersion->documento()->associate($documento);
        $docVersion->save();
        $parrafos = $this->parsearParrafos($vdt->getData('cuerpo'));
        foreach ($parrafos as $i => $parrafo) {
            $docParrafo = new ParrafoDocumento;
            $docParrafo->cuerpo = htmlspecialchars($parrafo, ENT_QUOTES);
            $docParrafo->ubicacion = $i;
            $docParrafo->version()->associate($docVersion);
            $docParrafo->save();
        }
        $this->flash('success', 'Se ha creado exitosamente una nueva versión del documento.');
        $this->redirectTo('shwDocumen', array('idDoc' => $documento->id));
    }

    public function verModificar($idDoc) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idDoc, new Validate\Rule\NumNatural());
        $categorias = Categoria::all()->toArray();
        $documento = Documento::with('contenido')->findOrFail($idDoc);
        $contenido = $documento->contenido;
        $datosDocumento = array_merge($contenido->toArray(), $documento->toArray());
        $this->render('contenido/documento/modificar.twig', array('documento' => $datosDocumento,
                                                                  'categorias' => $categorias));
    }

    public function modificar($idDoc) {
        $req = $this->request;
        $vdt = $this->validarOrganismo($req->post(), false);
        $documento = Documento::with('contenido')->findOrFail($idDoc);
        if ($documento->contenido->autor_id != $this->session->user('id')) {
            throw new BearableException('No puede modificar el documento de otro.');
        }
        $documento->descripcion = $vdt->getData('descripcion');
        $documento->save();
        $contenido = $documento->contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->categoria_id = $vdt->getData('categoria');
        $contenido->save();
        $this->flash('success', 'Los datos del documento fueron modificados exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function eliminar($idDoc) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idDoc, new Validate\Rule\NumNatural());
        $documento = Propuesta::with(array('contenido', 'comentarios.votos'))->findOrFail($idDoc);
        if ($documento->contenido->autor_id != $this->session->user('id')) {
            throw new BearableException('No puede eliminar el documento de otro.');
        }
        $documento->delete();
        $this->flash('success', 'Su documento fue eliminado exitosamente.');
        $this->redirect($req->getReferrer());
    }

    private function validarDocumento($data, $cuerpo = true) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('descripcion', new Validate\Rule\MinLength(8))
            ->addRule('descripcion', new Validate\Rule\MaxLength(1024))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addRule('categoria', new Validate\Rule\Exists('categorias'));
        if ($cuerpo) {
            $vdt->addRule('cuerpo', new Validate\Rule\MinLength(8))
                ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
                ->addFilter('cuerpo', FilterFactory::escapeHTML());
        }
        if (!$vdt->validate($data)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        return $vdt;
    }

    private function parsearParrafos($cuerpo) {
        $searchRx = array('~\[ul\](.*?)\[/ul\]~s',
                          '~\[ol\](.*?)\[/ol\]~s',
                          '~\[table\](.*?)\[/table\]~s');
        $replaceRx = array('<>[ul]$1[/ul]<>',
                           '<>[ol]$1[/ol]<>',
                           '<>[table]$1[/table]<>');
        $texto = preg_replace($searchRx, $replaceRx, $cuerpo);
        $parrafos = explode('<>', $texto);
        foreach ($parrafos as $parrafo) {
            if (strncmp($parrafo, '[ul]', 4) && strncmp($parrafo, '[ol]', 4) && strncmp($parrafo, '[table]', 7)) {
                $newParrafos = preg_split('~\R~s', $parrafo);
                foreach ($newParrafos as $newParrafo) {
                    if (!empty($newParrafo)) {
                        $final[] = $newParrafo;
                    }
                }
            } else {
                $final[] = $parrafo;
            }
        }
        return $final;
    }
}
