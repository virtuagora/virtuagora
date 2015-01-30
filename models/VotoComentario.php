<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoComentario extends Eloquent {
    protected $table = 'comentario_votos';
    protected $visible = array('id', 'cantidad', 'created_at', 'updated_at');
    protected $fillable = array('comentario_id', 'usuario_id');

    public function comentario() {
        return $this->belongsTo('Comentario');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
