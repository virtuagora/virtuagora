<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Propuesta extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'propuestas';

    public $incrementing = false;
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'contenido');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenido');
    }

    public function posturas() {
        return $this->belongsToMany('Usuario')->withPivot('tipo', 'publico')->withTimestamps();
    }
}
