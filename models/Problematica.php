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

    public static function boot() {
        parent::boot();
        static::deleting(function($problematica) {
            foreach ($problematica->comentarios as $comentario) {
                $comentario->delete();
            }
            $problematica->votos()->delete();
            $problematica->contenido->delete();
            return true;
        });
    }

}
