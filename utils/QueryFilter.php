<?php

class QueryFilter {

    public $query;
    public $filters;

    public function __construct($query, $params = array(), $filtrables = array()) {
        $this->filters = array();
        if (isset($params['where'])) {
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
                $this->query = $this->query->where($regla[0], $regla[1], $regla[2]);
            }
        } else {
            $this->query = $query;
        }
    }

}
