<?php use Augusthur\Validation as Validate;

class ContenidoCtrl extends Controller {

    use RestTrait;

    private $ordenables = array('id', 'puntos', 'created_at');
    private $filtrables = array('id', 'puntos');

    public function getRepresentation($conneg) {
        if (substr($conneg, 0, 16) == 'application/json') {
            return new JsonRepr();
        } else {
            throw new BearableException('Petición de formato de contenido no disponible.', 406);
        }
    }

    public function queryModel() {
        return Contenido::query();
    }

}
