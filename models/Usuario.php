<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Usuario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'usuarios';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'nombre', 'apellido', 'es_funcionario', 'es_jefe', 'partido_id', 'img_tipo', 'img_hash', 'puntos');
    //protected $hidden = array('password',  'tiene_avatar', 'token_verificacion', 'created_at', 'updated_at', 'deleted_at');

    public function partido() {
        return $this->belongsTo('Partido');
    }

    public function moderador() {
        return $this->hasOne('Moderador');
    }

}
