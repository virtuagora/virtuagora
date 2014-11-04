<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Partido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'partidos';

    protected $visible = array('id', 'nombre', 'acronimo', 'descripcion');

    public function creador() {
        return $this->belongsTo('Usuario');
    }

    public function afiliados() {
        return $this->hasMany('Usuario');
    }

    public function contacto() {
        return $this->morphOne('Contacto', 'contactable');
    }

    public static function boot() {
        parent::boot();
        static::created(function($partido) {
            Usuario::where('id', $partido->creador_id)->update(array('es_jefe' => true,
                                                                     'partido_id' => $partido->id));
        });
    }

}
