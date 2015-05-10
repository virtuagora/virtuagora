<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class ParrafoDocumento extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'documento_parrafos';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'ubicacion', 'comentarios');
    protected $with = array('comentarios');

    public function version() {
        return $this->belongsTo('VersionDocumento');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function getRaizAttribute() {
        return $this->version->documento;
    }

}
