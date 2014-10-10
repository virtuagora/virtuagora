<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Propuesta extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'propuestas';

    public $incrementing = false;
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'contenido', 'votos_favor', 'votos_contra', 'votos_neutro');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenido');
    }

    public function posturas() {
        return $this->belongsToMany('Usuario')->withPivot('tipo', 'publico')->withTimestamps();
    }
}
