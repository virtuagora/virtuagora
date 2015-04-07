<?php

class QueryMaker {

    public $query;
    public $params;
    private $operators = array('lt' => '<', 'le' => '<=', 'eq' => '=', 'ne' => '!=', 'ge' => '>=', 'gt' => '>');

    public function __construct($query, $params = array()) {
        $this->params = $params;
        $this->query = $query;
    }

    public function addFilters($filtrables = array()) {
        if (isset($this->params['where'])) {
            $filtros = explode(',', $this->params['where']);
            foreach ($filtros as $filtro) {
                $regla = explode('-', $filtro);
                if (count($regla) != 3) {
                    throw new BearableException('Parámetros de filtrado incorrectos.');
                }
                list($atr, $ope, $val) = $regla;
                if (!in_array($atr, $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                } else if (isset($this->operators[$ope])) {
                    $this->query = $this->query->where($atr, $this->operators[$ope], $val);
                } else if ($ope == 'in') {
                    $this->query = $this->query->wherein($atr, explode('.', $val));
                } else {
                    throw new BearableException('Operador inexistente.');
                }
            }
        }
        if (isset($this->params['where_null'])) {
            $filtros = explode(',', $this->params['where_null']);
            foreach ($filtros as $filtro) {
                if (!in_array($filtro, $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                $this->query = $this->query->whereNull($filtro);
            }
        }
        if (isset($this->params['where_not_null'])) {
            $filtros = explode(',', $this->params['where_not_null']);
            foreach ($filtros as $filtro) {
                if (!in_array($filtro, $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                $this->query = $this->query->whereNotNull($filtro);
            }
        }
    }

    public function addSorters($ordenables = array()) {
        if (isset($this->params['sort'])) {
            $sorters = explode(',', $this->params['sort']);
            foreach ($sorters as $sorter) {
                if (substr($sorter, 0, 1) == '-') {
                    $direction = 'DESC';
                    $sorter = substr($sorter, 1);
                } else {
                    $direction = 'ASC';
                }
                if (!in_array($sorter, $ordenables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                $this->query = $this->query->orderBy($sorter, $direction);
            }
        }
    }

}
