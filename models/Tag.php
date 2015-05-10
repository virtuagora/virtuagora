<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Tag extends Eloquent {

    //protected $table = 'tags';
    protected $visible = array('id', 'nombre', 'menciones');
    protected $fillable = array('nombre');

    public function contenidos() {
        return $this->morphedByMany('Contenido', 'taggable');
    }

    public function setNombreAttribute($value) {
        $this->attributes['nombre'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }

}
