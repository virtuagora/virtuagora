<?php use Augusthur\Validation as Validate;

trait RestTrait {

    abstract public function queryModel();
    abstract public function getRepresentation($conneg);

    public function listar() {
        $req = $this->request;
        $queryMaker = new QueryMaker($this->queryModel(), $req->get());
        $queryMaker->addFilters($this->filtrables);
        $queryMaker->addSorters($this->ordenables);
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($queryMaker->query, $url, $queryMaker->params);
        $repr = $this->getRepresentation($req->headers->get('ACCEPT'));
        $repr->shwCollection($this, $paginator);
    }

    public function ver($id) {
        $req = $this->request;
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($id, new Validate\Rule\NumNatural());
        $resource = $this->queryModel()->findOrFail($id);
        $repr = $this->getRepresentation($req->headers->get('ACCEPT'));
        $repr->shwResource($this, $resource);
    }

}
