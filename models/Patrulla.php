<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Patrulla extends Eloquent {

    //protected $table = 'patrullas';
    protected $visible = array('id', 'nombre', 'descripcion');

    public function moderadores() {
        return $this->hasMany('Usuario');
    }

    public function poderes() {
        return $this->belongsToMany('Poder');
    }

    public function getIdentidadAttribute() {
        return $this->attributes['nombre'];
    }

}
