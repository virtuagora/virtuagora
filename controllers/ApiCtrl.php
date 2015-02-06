<?php use Augusthur\Validation as Validate;

class ApiCtrl extends Controller {

    public function listar($recurso) {
        $req = $this->request;
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $recurso = ucfirst($recurso);
        $vdt->test($recurso, new Validate\Rule\InArray(array('Usuario')));
        $queryFilter = new QueryFilter(call_user_func($recurso.'::query'), $req->get());
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($queryFilter->query, $url, $req->get());

        $res = $this->response;
        $res->headers->set('Content-Type', 'application/json');
        if (!empty($paginator->links)) {
            $linkArray = array();
            foreach ($paginator->links as $rel => $link) {
                $linkArray[] = '<' . $link . '>; rel="' . $rel . '"';
            }
            $res->headers->set('Link', implode(', ', $linkArray));
        }

        echo $paginator->query->get()->toJson();
    }

}
