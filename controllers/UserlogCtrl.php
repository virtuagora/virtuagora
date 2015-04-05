<?php use Augusthur\Validation as Validate;

class UserlogCtrl extends RMRController {

    protected $mediaTypes = array('json');
    protected $properties = array('id');

    public function queryModel() {
        return Userlog::query();
    }

    public static function createLog($accion_id, $actor, $objeto, $tipoObj = null) {
        $log = new Userlog;
        $log->accion_id = $accion_id;
        $log->actor()->associate($actor);
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
