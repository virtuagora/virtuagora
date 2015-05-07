<?php

class AdminlogCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'poder_id'];

    public function queryModel($meth, $repr) {
        return Adminlog::query();
    }

    public static function createLog($comment, $poderId, $subclase, $actorId, $objeto, $tipoObj = null) {
        $log = new Adminlog;
        $log->descripcion = $comment;
        $log->subclase = $subclase;
        $log->poder_id = $poderId;
        $log->actor_id = $actorId;
        if (is_null($tipoObj)) {
            $log->objeto()->associate($objeto);
        } else {
            $log->objeto_id = $objeto;
            $log->objeto_type = $tipoObj;
        }
        $log->save();
        return $log;
    }

}
