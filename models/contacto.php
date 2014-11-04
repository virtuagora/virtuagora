<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Contacto extends Eloquent {
    //$table = 'contactos';

    protected $visible = array('id', 'email', 'telefono', 'web');

    public function contactable() {
        return $this->morphTo();
    }
}
