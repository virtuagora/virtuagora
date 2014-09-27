<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Usuario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'usuarios';

    protected $dates = ['deleted_at'];
    protected $hidden = array('suspendido',  'fecha_certificado');

}
