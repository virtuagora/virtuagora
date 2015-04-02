<?php

class QueryMaker {

    public $query;
    public $params;

    public function __construct($query, $params = array()) {
        $this->params = $params;
        $this->query = $query;
    }

    public function addFilters($filtrables = array()) {
        if (isset($this->params['where'])) {
            $filtros = explode(';', $this->params['where']);
            foreach ($filtros as $filtro) {
                $regla = explode(',', $filtro);
                if (count($regla) != 3) {
                    throw new BearableException('Parámetros de filtrado incorrectos.');
                }
                if (!in_array($regla[0], $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                switch ($regla[1]) {
                    case 'st': $regla[1] = '<'; break;
                    case 'se': $regla[1] = '<='; break;
                    case 'eq': $regla[1] = '='; break;
                    case 'ge': $regla[1] = '>='; break;
                    case 'gt': $regla[1] = '>'; break;
                    case 'ne': $regla[1] = '!='; break;
                    default: throw new BearableException('Operador inexistente.');
                }
                //TODO ver si se controla el contenido del filtro
                $this->query = $this->query->where($regla[0], $regla[1], $regla[2]);
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
                $this->query = $this->query->orderBy($sorter, $direction);
            }
        }
    }

}
