<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Partido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'partidos';

    protected $visible = array('id', 'nombre', 'imagen');

    public function creador() {
        return $this->belongsTo('Usuario');
    }

    public function afiliados() {
        return $this->hasMany('Usuario');
    }

}
