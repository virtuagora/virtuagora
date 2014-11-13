<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoProblematica extends Eloquent {
    $table = 'problematica_voto';

    protected $visible = array('id', 'postura', 'created_at', 'updated_at');

    public function problematica() {
        return $this->belongsTo('Problematica');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
