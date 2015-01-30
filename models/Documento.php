<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Documento extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'documento';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'descripcion', 'ultima_version');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function versiones() {
        return $this->hasMany('VersionDocumento');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($documento) {
            $documento->comentarios()->delete();
            $documento->contenido()->delete();
            return true;
        });
    }

}
