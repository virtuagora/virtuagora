<?php use Augusthur\Validation as Validate;

class ContenidoCtrl extends RMRController {

    protected $mediaTypes = array('json');
    protected $properties = array('id', 'puntos', 'created_at');

    public function queryModel() {
        return Contenido::query();
    }

}
