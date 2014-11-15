<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoProblematica extends Eloquent {
    protected $table = 'problematica_votos';
    protected $visible = array('id', 'postura', 'created_at', 'updated_at');
    protected $fillable = array('problematica_id', 'usuario_id');

    public function problematica() {
        return $this->belongsTo('Problematica');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
