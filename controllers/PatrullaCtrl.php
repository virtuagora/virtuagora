<?php use Augusthur\Validation as Validate;

class OrganismoCtrl extends RMRController {

    protected $mediaTypes = array('json', 'view');
    protected $properties = array('id', 'nombre', 'descripcion');

    public function queryModel() {
        return Patrulla::query();
    }

    public function executeListCtrl($paginator) {
        $patrullas = $paginator->rows;
        $nav = $paginator->links;
        $this->render('admin/patrullas.twig', array('patrullas' => $patrullas->toArray(),
                                                    'nav' => $nav));
    }

    public function executeGetCtrl($patrulla) {
        $this->notFound();
    }

    public function modificar($idPat) {
        $vdt = new Validate\Validator();
        $vdt->addRule('idPat', new Validate\Rule\NumNatural());
            ->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(2))
            ->addRule('nombre', new Validate\Rule\MaxLength(64))
            ->addRule('descripcion', new Validate\Rule\MaxLength(512));
        $req = $this->request;
        $data = array_merge(array('idPat' => $idPat), $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $patrulla = Patrulla::findOrFail($idPat);
        $patrulla->nombre = $vdt->getData('nombre');
        $patrulla->descripcion = $vdt->getData('descripcion');
        $patrulla->save();
        $this->flash('success', 'Los datos del grupo de moderación fueron modificados exitosamente.');
        $this->redirectTo('shwAdmPatrull');
    }

}
