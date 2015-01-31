<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Tag extends Eloquent {

    //protected $table = 'tags';
    protected $visible = array('id', 'nombre');

    public function contenidos() {
        return $this->belongsToMany('Contenido');
    }

}
