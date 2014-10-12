<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Patrulla extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'patrullas';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'nombre', 'descripcion');

    public function moderadores() {
        return $this->hasMany('Moderador');
    }

}
