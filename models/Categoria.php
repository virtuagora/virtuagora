<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Categoria extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'categorias';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'nombre');

    public function contenidos() {
        return $this->hasMany('Contenido');
    }

}
