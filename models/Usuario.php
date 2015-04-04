<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Usuario extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'usuarios';

    protected $dates = ['deleted_at'];
   // protected $visible = array('id', 'nombre', 'apellido', 'es_funcionario', 'es_jefe', 'partido_id', 'img_tipo', 'img_hash', 'puntos');
    protected $hidden = array('password', 'emailed_token', 'updated_at', 'deleted_at');

    public function partido() {
        return $this->belongsTo('Partido');
    }

    public function moderador() {
        return $this->hasOne('Moderador');
    }

    public function contenidos() {
        return $this->hasMany('Contenido', 'autor_id');
    }

    public function getNombreCompleto() {
        return $this->nombre.' '.$this->apellido;
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($usuario) {
            foreach ($usuario->contenidos as $contenido) {
                $contenido->contenible->delete();
            }
            $usuario->contacto->delete();
            $partido = Partido::where('usuario_id', $usuario->id)->first();
            if ($partido) {
                $partido->delete();
            }
            return true;
        });
    }

}
