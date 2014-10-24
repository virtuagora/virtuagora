<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'titulo', 'contenible_id', 'contenible_type', 'puntos', 'autor', 'created_at');
    protected $with = array('autor');

    public function contenible() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario', 'usuario_id');
    }
}
