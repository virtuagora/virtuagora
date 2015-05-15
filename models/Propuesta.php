<?php

class Propuesta extends Contenible {
    //protected $table = 'propuestas';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'votos_favor', 'votos_contra', 'votos_neutro');

    public function votos() {
        return $this->hasMany('VotoPropuesta');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($propuesta) {
            TagCtrl::updateTags($evento->contenido, array());
            foreach ($propuesta->comentarios as $comentario) {
                $comentario->delete();
            }
            $propuesta->votos()->delete();
            $propuesta->contenido->delete();
            return true;
        });
    }
}
