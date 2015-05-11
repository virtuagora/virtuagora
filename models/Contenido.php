<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Contenido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //$table = 'contenidos';

    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'titulo', 'contenible_id', 'contenible_type', 'impulsor_id', 'puntos', 'created_at',
                          'link', 'categoria', 'autor', 'contenible', 'tags', 'referido'];
    protected $appends = ['link'];
    protected $with = ['autor', 'categoria'];

    public function contenible() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public function impulsor() {
        return $this->belongsTo('Partido');
    }

    public function categoria() {
        return $this->belongsTo('Categoria');
    }

    public function tags() {
        return $this->morphToMany('Tag', 'taggable');
    }

    public function referido() {
        return $this->belongsTo('Contenido', 'referido_id');
    }

    public function referentes() {
        return $this->hasMany('Contenido', 'referido_id');
    }

    public function getLinkAttribute() {
        $name = 'shw' . substr($this->attributes['contenible_type'], 0, 7);
        $attr = ['id' . substr($this->attributes['contenible_type'], 0, 3) => $this->attributes['contenible_id']];
        $app = Slim\Slim::getInstance();
        return $app->request->getUrl() . $app->urlFor($name, $attr);
    }

    public function setTituloAttribute($value) {
        $this->attributes['titulo'] = $value;
        $this->attributes['huella'] = FilterFactory::calcHuella($value);
    }

    public function getIdentidadAttribute() {
        return $this->attributes['titulo'];
    }
}
