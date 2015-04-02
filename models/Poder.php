<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Poder extends Eloquent {

    protected $table = 'poderes';
    protected $visible = array('id', 'accion', 'patrulla_id', 'created_at', 'updated_at');

    public function patrulla() {
        return $this->belongsTo('Patrulla');
    }

}
