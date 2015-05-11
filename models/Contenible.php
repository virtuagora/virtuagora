<?php use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Contenible extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

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

    public function getIdentidadAttribute() {
        return $this->contenido->titulo;
    }

    public function getRaizAttribute() {
        return $this;
    }
}
