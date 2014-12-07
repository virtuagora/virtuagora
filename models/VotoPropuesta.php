<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoPropuesta extends Eloquent {
    protected $table = 'propuesta_votos';
    protected $visible = array('id', 'postura', 'publico', 'created_at', 'updated_at');
    protected $fillable = array('propuesta_id', 'usuario_id');

    public function propuesta() {
        return $this->belongsTo('Problematica');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
