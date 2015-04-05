<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Accion extends Eloquent {

    protected $table = 'acciones';
    protected $visible = array('id', 'nombre');
    protected $fillable = array('id', 'nombre');
    public $incrementing = false;

}
