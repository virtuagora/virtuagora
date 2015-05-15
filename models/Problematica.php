<?php

class Problematica extends Contenible {
    //protected $table = 'problematicas';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'cuerpo', 'afectados_directos', 'afectados_indirectos', 'afectados_indiferentes');

    public function votos() {
        return $this->hasMany('VotoProblematica');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($problematica) {
            TagCtrl::updateTags($evento->contenido, array());
            foreach ($problematica->comentarios as $comentario) {
                $comentario->delete();
            }
            $problematica->votos()->delete();
            $problematica->contenido->delete();
            return true;
        });
    }

}
