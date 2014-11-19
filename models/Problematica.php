<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Problematica extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'problematicas';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'afectados_directos', 'afectados_indirectos', 'afectados_indiferentes');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function votos() {
        return $this->hasMany('VotoProblematica');
    }

}
