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
        static::deleting(function($problemat) {
            TagCtrl::updateTags($problemat->contenido, array());
            foreach ($problemat->comentarios as $comentario) {
                $comentario->delete();
            }
            $problemat->votos()->delete();
            $problemat->contenido->delete();
            return true;
        });
    }

}
