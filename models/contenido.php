<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'titulo', 'contenido_type', 'puntos', 'autor');
    protected $with = array('autor');

    public function contenido() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }
}
