<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Accion extends Eloquent {

    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'acciones';
    protected $visible = array('id', 'nombre');
    protected $fillable = array('id', 'nombre');

}
