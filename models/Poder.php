<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Poder extends Eloquent {

    public $timestamps = false;
    protected $table = 'poderes';
    protected $visible = array('id', 'nombre', 'descripcion');
    protected $fillable = array('nombre', 'descripcion');

    public function patrullas() {
        return $this->belongsToMany('Patrulla');
    }

}
