<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Comentario extends Eloquent {
    //$table = 'comentarios';

    protected $visible = array('id', 'cuerpo', 'comentable_type', 'votos', 'created_at', 'updated_at', 'autor', 'respuestas');
    protected $with = array('autor', 'respuestas');

    public function comentable() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function respuestas() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function votos() {
        return $this->hasMany('VotoComentario');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($comentario) {
            $comentario->respuestas()->delete();
            $comentario->votos()->delete();
            return true;
        });
    }

}
