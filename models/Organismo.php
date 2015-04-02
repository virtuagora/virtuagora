<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Organismo extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'organismos';

    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'nombre', 'cupo', 'funcionarios_count');
    protected $appends = array('funcionarios_count');

    public function funcionarios() {
        return $this->hasMany('Funcionario');
    }

    public function usuarios() {
        return $this->belongsToMany('Usuario', 'funcionarios')->whereNull('funcionarios.deleted_at')->withTimestamps();
    }

    public function contacto() {
        return $this->morphOne('Contacto', 'contactable');
    }

    public function getFuncionariosCountAttribute() {
        return $this->funcionarios->count();
    }

}
