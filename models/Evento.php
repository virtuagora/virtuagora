<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Evento extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'eventos';
    protected $dates = ['deleted_at', 'fecha'];
    protected $visible = ['id', 'cuerpo', 'fecha', 'lugar'];

    public function scopeModifiableBy($query, $id) {
        return $query->whereHas('contenido', function($q) use ($id) {
            $q->where('autor_id', $id);
        });
    }

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function usuarios() {
        return $this->belongsToMany('Usuario', 'evento_usuario')->withPivot('presente', 'publico')->withTimestamps();
    }

    public function getNombreAttribute() {
        return $this->contenido->titulo;
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($evento) {
            foreach ($evento->comentarios as $comentario) {
                $comentario->delete();
            }
            $evento->usuarios()->detach();
            $evento->contenido->delete();
            return true;
        });
    }
}
