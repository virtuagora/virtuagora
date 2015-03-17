<?php use Augusthur\Validation as Validate;

class Paginator {

    public $query;
    public $rows;
    public $links;

    public static function validate($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('page', new Validate\Rule\NumNatural())
            ->addRule('page', new Validate\Rule\NumMin(1))
            ->addRule('take', new Validate\Rule\NumNatural())
            ->addRule('take', new Validate\Rule\NumMin(1))
            ->addOptional('page')
            ->addOptional('take');
        if (!$vdt->validate($data)) {
            throw new BearableException('Parámetros de paginación incorrectos.');
        }
        return $vdt;
    }

    public function __construct($query, $url = '', $params = array()) {
        $page = isset($params['page']) ? $params['page'] : 1;
        $take = isset($params['take']) ? $params['take'] : 10;
        $count = isset($params['count']) ? $params['count'] : true;
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
