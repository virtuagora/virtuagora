<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Comentario extends Eloquent {
    //$table = 'comentarios';

    protected $visible = array('id', 'cuerpo', 'comentable_type', 'votos', 'created_at', 'updated_at', 'autor');
    protected $with = array('autor');

    public function comentable() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

}
