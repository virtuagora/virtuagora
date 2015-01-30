<?php use Augusthur\Validation as Validate;

class ApiCtrl extends Controller {

    private $base;
    private $query;
    private $headers = array();
    private $params;
    private $filters;

    public function crear($query, $parametros, $filtrables, $ordenables) {

        $vdt = new Validate\Validator();
        $vdt->addRule('sort', new Validate\Rule\Regex('/^[+|-]?[a-z]+$/'))
            ->addRule('page', new Validate\Rule\NumNatural())
            ->addRule('take', new Validate\Rule\NumNatural());
        if (!$vdt->validate($parametros)) {
            throw (new TurnbackException())->setErrors($vdt->getErrors()); //TODO Error api
        }
        $this->params = $vdt->getData();

        $queryFilter = new QueryFilter($query, $this->params);

        $req = $this->request;
        $url = $req->getUrl().$req->getPath();
        $paginator = new Paginator($queryFilter->query, $url, $this->params, $vdt->getData('page'), $vdt->getData('take'));

        if (!empty($paginator->links)) {
            $linkArray = array();
            foreach ($paginator->links as $rel => $link) {
                $linkArray[] = '<' . $link . '>; rel="' . $rel . '"';
            }
            $this->addHeader(array('Link' => implode(', ', $linkArray)));
        }

        $this->filters = array();
        if (isset($this->params['where'])) {
            $filtros = explode(';', $this->params['where']);
            foreach ($filtros as $filtro) {
                $regla = explode(',', $filtro);
                if (count($regla) != 3) {
                    //TODO tirar exception
                }
                if (!in_array($regla[0], $filtrables)) {
                    //TODO tirar exception
                }
                switch ($regla[1]) {
                    case 'st': $regla[1] = '<'; break;
                    case 'se': $regla[1] = '<='; break;
                    case 'eq': $regla[1] = '='; break;
                    case 'ge': $regla[1] = '>='; break;
                    case 'gt': $regla[1] = '>'; break;
                    case 'ne': $regla[1] = '!='; break;
                    //TODO default: tirar error api;
                }
                //TODO ver si se controla el contenido del filtro
                $this->filters[] = $regla;
            }
        }



        //TODO inicializar, validar y agregar filtros
        $this->base = $base . '/' . $recurso;
        if (isset($parametros['where'])) {
            // validar filtros
            $this->addFilters($parametros['where']);
            $this->params['filter']['where'] = $parametros['where'];
        } else {
            $this->query = call_user_func($recurso . '::all');
        }
        $this->paginate($parametros['page'], $parametros['take']);
        if (isset($parametros['sort'])) {
            $sorter = $parametros['sort']; // controlar orden +-
            if (in_array($sorter, $ordenables)) {
                $this->query = $this->query->sort($sorter); //revisar
            }
        }
        // agregrar params de sort y paginate
    }

    public function getHeaders() {
        //TODO
    }

    public function getResult() {
        return $this->query->get()->toJson();
    }

    public function paginate($page, $take) {
        $this->query = $this->query->skip(($page-1)*$take)->take($take);
        $records = $query->count();
        $links = array();
        if ($page*$take < $records) {
            $links[] = '<' . $this->createLink(array('page'=>$page+1,'take'=$take)) . '; rel="next"';
            //$links[] = '<' . $baseUrl . '?page=' . $page+1 . '&take=' . $take . '&' . $parameters . '>; rel="next"';
            $links[] = '<' . $baseUrl . '?page=' . ceil($records/$page) . '&take=' . $take . '&' . $parameters . '>; rel="last"';
        }
        if ($page > 1) {
            $links[] = '<' . $baseUrl . '?page=' . $page-1 . '&take=' . $take . '&' . $parameters . '>; rel="prev"';
            $links[] = '<' . $baseUrl . '?page=1&take=' . $take . '&' . $parameters . '>; rel="first"';
        }
        $this->addHeaders(array('Link' => implode(', ', $links)));
    }

    public function createLink($extraParams = array()) {
        // si parametros null -> llenar con $this->params
        $parametros = array_merge(getArrayValues($this->params)); // ver
        foreach ($extraParams as $paramKey => $paramVal) {
            $parametros[$paramKey] = $paramVal;
        }
        return $this->base . '?' . implode('&', $parametros); // tiene que ser implode(clave'='valor)
    }

    public function addHeaders($headers = array()) {
        foreach ($headers as $headerKey => $headerVal) {
            $this->headers[$headerKey] = $headerVal;
        }
    }
    //Link: <https://api.github.com/user/repos?page=3&per_page=100>; rel="next", <https://api.github.com/user/repos?page=50&per_page=100>; rel="last"


}
