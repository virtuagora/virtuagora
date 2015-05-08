<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Adminlog extends Eloquent {

    //protected $table = 'adminlogs';
    protected $visible = array('id', 'descripcion', 'poder_id', 'created_at', 'mensaje');
    protected $appends = array('mensaje');
    protected $with = array('actor', 'objeto');

    public function poder() {
        return $this->belongsTo('Poder', 'poder_id');
    }

    public function actor() {
        return $this->belongsTo('Usuario');
    }

    public function objeto() {
        return $this->morphTo()->withTrashed();
    }

    public function getMensajeAttribute() {
        $app = Slim\Slim::getInstance();
        return $app->translator->trans('log.admin'.$this->poder_id.'.'.$this->subclase,
                                       ['%act%' => $this->actor_name,
                                        '%url%' => $this->objeto_link,
                                        '%obj%' => $this->objeto_name,
                                        '%des%' => $this->descripcion]);
    }

    public function getObjetoLinkAttribute() {
        if ($this->attributes['objeto_id']) {
            $name = 'shw' . substr($this->attributes['objeto_type'], 0, 7);
            $attr = ['id' . substr($this->attributes['objeto_type'], 0, 3) => $this->attributes['objeto_id']];
            $app = Slim\Slim::getInstance();
            return $app->request->getUrl() . $app->urlFor($name, $attr);
        } else {
            return '';
        }
    }

    public function getActorNameAttribute() {
        return htmlspecialchars($this->actor->identidad, ENT_QUOTES);
    }

    public function getObjetoNameAttribute() {
        return $this->objeto->identidad;
    }

}
