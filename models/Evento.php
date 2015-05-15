<?php

class Evento extends Contenible {
    //protected $table = 'eventos';
    protected $dates = ['deleted_at', 'fecha'];
    protected $visible = ['id', 'cuerpo', 'fecha', 'lugar'];

    public function usuarios() {
        return $this->belongsToMany('Usuario', 'evento_usuario')->withPivot('presente', 'publico')->withTimestamps();
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($evento) {
            TagCtrl::updateTags($evento->contenido, array());
            foreach ($evento->comentarios as $comentario) {
                $comentario->delete();
            }
            $evento->usuarios()->detach();
            $evento->contenido->delete();
            return true;
        });
    }
}
