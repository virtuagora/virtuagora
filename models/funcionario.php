<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Funcionario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'funcionarios';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'organismo_id', 'usuario_id');

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

    public function organismo() {
        return $this->belongsTo('Organismo');
    }

}
