<?php use Augusthur\Validation as Validate;

class ContenidoCtrl extends RMRController {

    protected $mediaTypes = array('json');
    protected $properties = array('id', 'puntos', 'created_at', 'contenible_type', 'contenible_id');

    public function queryModel($meth, $repr) {
        switch ($meth) {
            case 0: return Contenido::query();
            case 1: return Contenido::with('contenible');
        }
    }

}
