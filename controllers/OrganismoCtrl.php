<?php use Augusthur\Validation as Validate;

class OrganismoCtrl extends RMRController {

    protected $mediaTypes = array('json');
    protected $properties = array('id', 'nombre', 'cupo', 'funcionarios_count');

    public function queryModel() {
        return Organismo::query();
    }

}
