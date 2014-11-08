<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Categoria extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'categorias';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'nombre');


    public function contenidos() {
        return $this->belongsToMany('Contenido');
    }

}
