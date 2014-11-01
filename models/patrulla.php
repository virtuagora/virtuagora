<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Patrulla extends Eloquent {
    //$table = 'patrullas';

    protected $visible = array('id', 'nombre', 'descripcion');

    public function moderadores() {
        return $this->hasMany('Moderador');
    }

}
