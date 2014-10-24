<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Moderador extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'moderadores';
    protected $dates = ['deleted_at'];
    public $incrementing = false;

    public function usuario() {
        return $this->belongsTo('Usuario', 'id');
    }

    public function patrulla() {
        return $this->belongsTo('Patrulla');
    }
}
