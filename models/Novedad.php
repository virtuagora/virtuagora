<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Novedad extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'novedades';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($novedad) {
            $novedad->comentarios()->delete();
            $novedad->contenido->delete();
            return true;
        });
    }

}
