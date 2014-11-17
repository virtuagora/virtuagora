<?php use Augusthur\Validation as Validate;

class PartidoCtrl extends Controller {

    public function showPartidos() {
        $partidos = Partido::all();
        $this->render('partido/listar.twig', array('partidos' => $partidos->toArray()));
    }

    public function showCrearPartido() {
        $this->render('partido/crear.twig');
    }

    public function crearPartido() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('acronimo', new Validate\Rule\Alpha())
            ->addRule('acronimo', new Validate\Rule\MinLength(2))
            ->addRule('acronimo', new Validate\Rule\MaxLength(8))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512))
            ->addRule('fundador', new Validate\Rule\Alpha(array(' ')))
            ->addRule('fundador', new Validate\Rule\MaxLength(32))
            ->addRule('fecha', new Validate\Rule\Date())
            ->addRule('url', new Validate\Rule\URL())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('telefono', new Validate\Rule\Telephone())
            ->addFilter('descripcion', 'htmlspecialchars');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        if ($usuario->partido_id) {
            throw (new TurnbackException())->setErrors(array('No es posible crear un partido si ya está afilado a otro.'));
        }
        $partido = new Partido;
        $partido->nombre = $vdt->getData('nombre');
        $partido->acronimo = $vdt->getData('acronimo');
        $partido->descripcion = $vdt->getData('descripcion');
        $partido->fundador = $vdt->getData('fundador');
        $partido->fecha_fundacion = $vdt->getData('fecha');
        $partido->creador_id = $this->session->user('id');
        $partido->creador()->associate($usuario);
        $partido->save();
        if ($vdt->getData('email') || $vdt->getData('url') || $vdt->getData('telefono')) {
            $contacto = new Contacto;
            $contacto->email = $vdt->getData('email');
            $contacto->web = $vdt->getData('url');
            $contacto->telefono = $vdt->getData('telefono');
            $contacto->contactable()->associate($partido);
            $partido->save();
        }
        $this->crearImagen($partido->id, $partido->nombre);
        $this->redirect($req->getRootUri().'/partido');
    }

    private function crearImagen($id, $nombre) {
        $dir = 'img/partido/' . $id;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $hash = md5(strtolower(trim($nombre)));
        foreach (array(32, 64, 160) as $res) {
            $ch = curl_init('http://www.gravatar.com/avatar/'.$hash.'?d=identicon&f=y&s='.$res);
            $fp = fopen($dir . '/' . $res . '.png', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
    }

    public function unirsePartido($idPar) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPar', new Validate\Rule\NumNatural());
        if (!$vdt->validate(array('idPar' => $idPar))) {
            $this->notFound();
        }
        $partido = Partido::findOrFail($idPar);
        $usuario = $this->session->getUser();
        if ($usuario->partido) {
            throw (new TurnbackException())->setErrors(array('Usted ya está afiliado a otro partido.'));
        }
        $usuario->partido()->associate($partido);
        $usuario->save();
        $this->session->setUser($usuario);
        $this->redirect($this->request->getRootUri().'/partido');
    }

    public function dejarPartido() {
        $usuario = $this->session->getUser();
        $partido = $usuario->partido;
        if (!$partido) {
            throw new BearableException('Usted no pertenece a ningún partido.');
        }
        if ($partido->creador_id == $usuario->id) {
            throw new BearableException('Usted no puede dejar el partido que creó.');
        }
        $usuario->partido()->dissociate();
        $usuario->save();
        $this->session->setUser($usuario);
        $this->redirect($this->request->getRootUri().'/partido');
    }

    public function showModificarPartido($idPar) {
        $partido = Partido::with('contacto')->findOrFail($idPar);
        $datosPartido = $partido->toArray();
        $datosPartido['contacto'] = $partido->contacto ? $partido->contacto->toArray() : null;
        $this->render('partido/modificar.twig', array('partido' => $datosPartido));
    }

    public function modificarPartido() {
        $this->flash('success', 'Su contraseña fue modificada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function cambiarImagen($idPar) {
        $dir = 'img/partido/' . $idPar;
        if (!is_dir($dir)) {
            mkdir('$dir', 0777, true);
        }
        $storage = new \Upload\Storage\FileSystem($dir, true);
        $file = new \Upload\File('imagen', $storage);
        $filename = 'original';
        $file->setName($filename);
        $file->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/png', 'image/jpg', 'image/jpeg', 'image/gif')),
            new \Upload\Validation\Size('1M')
        ));
        $file->upload();
        foreach (array(32, 64, 160) as $res) {
            $image = new ZebraImage();
            $image->source_path = $dir . '/' . $file->getNameWithExtension();
            $image->target_path = $dir . '/' . $res . '.png';
            $image->preserve_aspect_ratio = true;
            $image->enlarge_smaller_images = true;
            $image->preserve_time = true;
            $image->resize($res, $res, ZEBRA_IMAGE_CROP_CENTER);
        }
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

}
