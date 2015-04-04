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
            $filtros = explode(' ', $this->params['where']);
            foreach ($filtros as $filtro) {
                $regla = explode('-', $filtro);
                if (count($regla) != 3) {
                    throw new BearableException('Parámetros de filtrado incorrectos.');
                }
                if (!in_array($regla[0], $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                $match = true;
                switch ($regla[1]) {
                    case 'lt': $regla[1] = '<'; break;
                    case 'le': $regla[1] = '<='; break;
                    case 'eq': $regla[1] = '='; break;
                    case 'ge': $regla[1] = '>='; break;
                    case 'gt': $regla[1] = '>'; break;
                    case 'ne': $regla[1] = '!='; break;
                    default: $match = false;
                }
                // TODO ver si se controla el contenido del filtro
                if ($match) {
                    $this->query = $this->query->where($regla[0], $regla[1], $regla[2]);
                } else if ($regla[1] == 'in') {
                    $this->query = $this->query->wherein($regla[0], explode('.', $regla[2]));
                } else {
                    throw new BearableException('Operador inexistente.');
                }
            }
        }
        if (isset($this->params['where_null'])) {
            $filtros = explode(' ', $this->params['where_null']);
            foreach ($filtros as $filtro) {
                if (!in_array($filtro, $filtrables)) {
                    throw new BearableException('Filtro inexistente.');
                }
                $this->query = $this->query->whereNull($filtro);
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
