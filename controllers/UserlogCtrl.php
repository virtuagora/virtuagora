<?php use Augusthur\Validation as Validate;

class UserlogCtrl extends RMRController {

    protected $mediaTypes = ['json'];
    protected $properties = ['id', 'accion_id', 'actor_name', 'objeto_name', 'objeto_link'];
    public static $messages = ['es' => ['newPropues' => '%s creó la propuesta: <a href="%s">%s</a>.',
                                        'modPropues' => '%s modificó la propuesta: <a href="%s">%s</a>.',
                                        'votPropues' => '%s voto en la propuesta: <a href="%s">%s</a>.',
                                        'newProblem' => '%s creó la problemática: <a href="%s">%s</a>.',
                                        'votProblem' => '%s voto en la problemática: <a href="%s">%s</a>.',
                                        'newDocumen' => '%s publicó el documento colaborativo: <a href="%s">%s</a>.',
                                        'newNovedad' => '%s publicó la novedad: <a href="%s">%s</a>.',
                                        'newEventoo' => '%s anunció el evento: <a href="%s">%s</a>.',
                                        'modEventoo' => '%s modificó datos del evento: <a href="%s">%s</a>.',
                                        'newComenta' => '%s comentó en: <a href="%s">%s</a>.',
                                        'newPartido' => '%s creó el partido: <a href="%s">%s</a>.',
                                        'delPartido' => '%s eliminó el partido: <a href="%s">%s</a>.',
                                        'joiPartido' => '%s se afilió a <a href="%s">%s</a>.',
                                        'lefPartido' => '%s abandonó a <a href="%s">%s</a>.',
                                        'newJefPart' => '%s ahora es jefe del <a href="%s">%s</a>.',
                                        'delJefPart' => '%s dejó de ser jefe del <a href="%s">%s</a>.']
                              ];

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
