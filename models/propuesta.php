<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Propuesta extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'propuestas';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'votos_favor', 'votos_contra', 'votos_neutro');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function posturas() {
        return $this->belongsToMany('Usuario')->withPivot('postura', 'publico')->withTimestamps();
    }
}
