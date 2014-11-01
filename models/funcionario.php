<?php use Illuminate\Database\Eloquent\Model as Eloquent;

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

    public static function boot() {
        parent::boot();
        static::creating(function($funcionario) {
            $usuario = Usuario::find($funcionario->usuario_id);
            if (is_null($usuario)) {
                return false;
            } else {
                $usuario->es_funcionario = true;
                $usuario->save();
                return true;
            }
        });
    }

}
