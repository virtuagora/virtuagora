<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Moderador extends Eloquent {
    protected $table = 'moderadores';
    public $incrementing = false;

    public function usuario() {
        return $this->belongsTo('Usuario', 'id');
    }

    public function patrulla() {
        return $this->belongsTo('Patrulla');
    }
}
