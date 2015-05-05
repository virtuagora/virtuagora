<?php use Augusthur\Validation as Validate;

abstract class RMRController extends Controller {

    protected $mediaTypes = array();
    protected $properties = array();
    protected $searchable = false;

    abstract public function queryModel($meth, $repr);

    public function listar() {
        $req = $this->request;
        $repr = $this->negotiateContent($req->headers->get('ACCEPT'));
        $queryMaker = new QueryMaker($this->queryModel(0, $repr->getName()), $req->get());
        $queryMaker->addFilters($this->properties, $this->searchable);
        $queryMaker->addSorters($this->properties);
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($queryMaker->query, $url, $queryMaker->params);
        $repr->shwCollection($this, $paginator);
    }

    public function ver($id) {
        $req = $this->request;
        $repr = $this->negotiateContent($req->headers->get('ACCEPT'));
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($id, new Validate\Rule\NumNatural());
        $resource = $this->queryModel(1, $repr->getName())->findOrFail($id);
        $repr->shwResource($this, $resource);
    }

    public function negotiateContent($accept) {
        if (substr($accept, 0, 16) == 'application/json') {
            $this->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            $this->api = true;
            $repr = new JsonRepr();
        } else {
            $repr = new ViewRepr();
        }
        if (!in_array($repr->getName(), $this->mediaTypes)) {
            throw new BearableException('Petición de formato de contenido no disponible.', 406);
        }
        return $repr;
    }

}
