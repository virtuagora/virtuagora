<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Ciudadano extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'ciudadanos';

    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $hidden = array('password',  'tiene_avatar', 'token_verificacion');

    public function usuario() {
        return $this->belongsTo('Usuario', 'id');
    }
}
