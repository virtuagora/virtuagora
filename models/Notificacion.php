<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Notificacion extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    public $timestamps = false;
    protected $table = 'notificaciones';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'usuario_id', 'fecha', 'mensaje'];
    protected $appends = ['mensaje', 'fecha'];
    protected $with = ['notificable'];

    public function notificable() {
        return $this->morphTo();
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

    public function getFechaAttribute() {
        return $this->notificable->updated_at;
    }

    public function getMensajeAttribute() {
        return $this->notificable->mensaje;
    }

}
