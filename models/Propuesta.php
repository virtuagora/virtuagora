<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Propuesta extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'propuestas';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'votos_favor', 'votos_contra', 'votos_neutro');

    public function scopeModifiableBy($query, $id) {
        return $query->whereHas('contenido', function($q) use ($id) {
            $q->where('autor_id', $id);
        });
    }

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible')->withTrashed();
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable');
    }

    public function votos() {
        return $this->hasMany('VotoPropuesta');
    }

    public function getNombreAttribute() {
        return $this->contenido->titulo;
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($propuesta) {
            foreach ($propuesta->comentarios as $comentario) {
                $comentario->delete();
            }
            $propuesta->votos()->delete();
            $propuesta->contenido->delete();
            return true;
        });
    }
}
