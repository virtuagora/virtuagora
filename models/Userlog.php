<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Userlog extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'userlogs';
    protected $visible = array('id', 'accion_id', 'url_nombre', 'url_params', 'obj_nombre');
    protected $appends = array('url_nombre', 'url_params', 'obj_nombre');
    protected $with = array('objeto');

    public function accion() {
        return $this->belongsTo('Accion', 'accion_id');
    }

    public function actor() {
        return $this->belongsTo('Usuario');
    }

    public function objeto() {
        return $this->morphTo();
    }

    public function getUrlNombreAttribute() {
        return 'shw' . substr($this->objeto_type, 0, 7);
    }

    public function getUrlParamsAttribute() {
        return array('id' . substr($this->objeto_type, 0, 3) => $this->objeto_id);
    }

    public function getObjNombreAttribute() {
        return $this->objeto->nombre ?: $this->objeto->titulo;
    }

}
