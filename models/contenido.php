<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'titulo', 'contenido_type', 'puntos');

    public function contenido() {
        return $this->morphTo();
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }
}
