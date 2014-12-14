<?php use Augusthur\Validation as Validate;

class DocumentoCtrl extends Controller {

    public function verCrear() {
        $categorias = Categoria::all();
        $this->render('contenido/documento/crear.twig', array('categorias' => $categorias->toArray()));
    }

    public function crear() {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(8))
            ->addRule('titulo', new Validate\Rule\MaxLength(128))
            ->addRule('descripcion', new Validate\Rule\MinLength(8))
            ->addRule('descripcion', new Validate\Rule\MaxLength(1024))
            ->addRule('cuerpo', new Validate\Rule\MinLength(8))
            ->addRule('cuerpo', new Validate\Rule\MaxLength(8192))
            ->addRule('categoria', new Validate\Rule\NumNatural())
            ->addFilter('cuerpo', FilterFactory::escapeHTML());
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $autor = $this->session->getUser();

        $documento = new Propuesta;
        $propuesta->cuerpo = htmlspecialchars($req->post('cuerpo'), ENT_QUOTES);
        $propuesta->votos_favor = 0;
        $propuesta->votos_contra = 0;
        $propuesta->votos_neutro = 0;
        $propuesta->save();
        $contenido = new Contenido;
        $contenido->titulo = htmlspecialchars($req->post('titulo'));
        $contenido->puntos = 0;
        $contenido->categoria_id = $vdt->getData('categoria'); // TODO controlar que existe esa categoria
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($propuesta);
        $contenido->save();
        $this->redirectTo('shwPropues', array('idPro' => $propuesta->id));
    }

}
