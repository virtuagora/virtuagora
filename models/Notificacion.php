<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Notificacion extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'notificaciones';
    protected $dates = array('deleted_at');
    protected $visible = array('id', 'usuario_id', 'updated_at', 'mensaje');
    protected $appends = array('mensaje');
    protected $with = array('notificable');

    public function notificable() {
        return $this->morphTo();
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

    public function getMensajeAttribute() {
        return $this->notificable->mensaje;
    }

}
