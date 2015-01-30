<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Accion extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'acciones';
    protected $dates = array('deleted_at');
    protected $visible = array('id', 'tipo', 'url_nombre', 'url_params', 'obj_nombre');
    protected $appends = array('url_nombre', 'url_params', 'obj_nombre');
    protected $with = array('objeto');

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
