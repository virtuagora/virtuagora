<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Comentario extends Eloquent {
    //$table = 'comentarios';

    protected $visible = array('id', 'cuerpo', 'autor_id', 'comentable_type', 'votos', 'created_at', 'updated_at');

    public function comentable() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

}
