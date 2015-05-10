<?php

class Novedad extends Contenible {
    protected $table = 'novedades';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'cuerpo'];

    public static function boot() {
        parent::boot();
        static::deleting(function($novedad) {
            foreach ($novedad->comentarios as $comentario) {
                $comentario->delete();
            }
            $novedad->contenido->delete();
            return true;
        });
    }
}
