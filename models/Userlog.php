<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Userlog extends Eloquent {

    //protected $table = 'userlogs';
    protected $visible = array('id', 'accion_id', 'actor_name', 'objeto_name', 'objeto_link');
    protected $appends = array('actor_name', 'objeto_name', 'objeto_link');
    protected $with = array('actor', 'objeto');

    public function accion() {
        return $this->belongsTo('Accion', 'accion_id');
    }

    public function actor() {
        return $this->belongsTo('Usuario');
    }

    public function objeto() {
        return $this->morphTo();
    }

    public function getObjetoLinkAttribute() {
        if ($this->attributes['objeto_id']) {
            $name = 'shw' . substr($this->attributes['objeto_type'], 0, 7);
            $attr = ['id' . substr($this->attributes['objeto_type'], 0, 3) => $this->attributes['objeto_id']];
            return Slim\Slim::getInstance()->urlFor($name, $attr);
        } else {
            return '';
        }
    }

    public function getActorNameAttribute() {
        return $this->actor->nombre_completo;
    }
/*
    public function getUrlNombreAttribute() {
        return 'shw' . substr($this->objeto_type, 0, 7);
    }

    public function getUrlParamsAttribute() {
        return array('id' . substr($this->objeto_type, 0, 3) => $this->objeto_id);
    }
*/
    public function getObjetoNameAttribute() {
        return $this->objeto->nombre;
    }

}
