<?php use Augusthur\Validation as Validate;

class Paginator {

    public $query;
    public $rows;
    public $links;

    public function validate($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('page', new Validate\Rule\NumNatural())
            ->addRule('page', new Validate\Rule\NumMin(1))
            ->addRule('take', new Validate\Rule\NumNatural())
            ->addRule('take', new Validate\Rule\NumMin(1))
            ->addRule('count', new Validate\Rule\InArray(true, false)) // TODO ver si filtro
            ->addOptional('page')
            ->addOptional('take')
            ->addOptional('count');
        if (!$vdt->validate($data)) {
            throw new BearableException('Parámetros de paginación incorrectos.');
        }
        return $vdt;
    }

    public function __construct($query, $url = '', $params = array()) {
        $vdt = $this->validate($params);
        $page = $vdt->getData('page') ?: 1;
        $take = $vdt->getData('take') ?: 10;
        $count = $vdt->getData('count') ?: true;
        if ($count) {
            $lastPage = ceil($query->count()/$take);
            $page = min($page, $lastPage);
            $this->query = $query->skip(($page-1)*$take)->take($take);
            $this->rows = $this->query->get();
            $moreRows = ($page < $lastPage);
        } else {
            $this->query = $query->skip(($page-1)*$take)->take($take+1);
            $this->rows = $this->query->get();
            $moreRows = ($this->result->count() > $take);
        }
        $this->links = array();
        if ($moreRows) {
            $params['page'] = $page+1;
            $this->links['next'] = $url . '?' . http_build_query($params);
            if ($count) {
                $params['page'] = $lastPage;
                $this->links['last'] = $url . '?' . http_build_query($params);
            } else {
                $this->rows->pop();
            }
        }
        if ($page > 1) {
            $params['page'] = $page-1;
            $this->links['prev'] = $url . '?' . http_build_query($params);
            $params['page'] = 1;
            $this->links['first'] = $url . '?' . http_build_query($params);
        }
    }

}
