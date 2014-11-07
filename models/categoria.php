<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Categoria extends Eloquent {
    //$table = 'categorias';

    protected $visible = array('id', 'nombre');

    public function contenidos() {
        return $this->belongsToMany('Contenido');
    }

}
