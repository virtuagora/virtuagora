<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'titulo', 'contenible_id', 'contenible_type', 'impulsor_id', 'puntos', 'autor', 'created_at');
    protected $with = array('autor', 'categoria');

    public function scopeModifiableBy($query, $id) {
        return $query->where('autor_id', $id);
    }

    public function contenible() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function impulsor() {
        return $this->belongsTo('Partido');
    }

    public function categoria() {
        return $this->belongsTo('Categoria');
    }

    public function tags() {
        return $this->belongsToMany('Tag');
    }
}
