<?php

class UserlogCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'accion_id'];

    public function queryModel($meth, $repr) {
        return Userlog::query();
    }

    public static function createLog($accionId, $actor, $objeto, $tipoObj = null) {
        $log = new Userlog;
        $log->accion_id = $accionId;
        if (is_numeric($actor)) {
            $log->actor_id = $actor;
        } else {
            $log->actor()->associate($actor);
        }
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
