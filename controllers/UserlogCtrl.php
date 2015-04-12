<?php use Augusthur\Validation as Validate;

class UserlogCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'accion_id', 'actor_name', 'objeto_name', 'objeto_link'];
    public static $messages = ['es' => ['modPropues' => '%s modificó la propuesta <a href="%s">%s</a>',
                                        'joiPartido' => '%s se afilió a <a href="%s">%s</a>']
                              ];

/*
newComenta
newDocumen
newNovedad
newPartido
joiPartido
lefPartido
newJefPart
delJefPart
votProblem
newProblem
votPropues
newPropues
modPropues
*/

    public function queryModel($meth, $repr) {
        return Userlog::query();
    }

    public static function getMessage($usrLog, $lang = 'es') {
        //if (isset(self::$messages[$lang][$usrLog->accion_id])) {
        if (isset(self::$messages[$lang][$usrLog->accion_id])) {
            return sprintf(self::$messages[$lang][$usrLog->accion_id],
                           $usrLog->actor_name, $usrLog->objeto_link, $usrLog->objeto_name);
        } else {
            return 'ERROR';
        }
    }

    public static function createLog($accionId, $actor, $objeto, $tipoObj = null) {
        $log = new Userlog;
        $log->accion_id = $accionId;
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
