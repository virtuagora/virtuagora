<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Imagen extends Eloquent {
    $table = 'imagenes';

    protected $visible = array('id', 'titulo', 'extension', 'autor_id', 'created_at');

    public function imagenable() {
        return $this->morphTo();
    }

    public function autor() {
        return $this->belongsTo('Usuario');
    }

    public static function boot() {
        parent::boot();
        static::deleted(function($imagen) {
            $f1 = 'img/imagenes/'.$imagen->autor_id.'/orig/'.$imagen->id.'.'.$imagen->extension;
            $f2 = 'img/imagenes/'.$imagen->autor_id.'/thum/'.$imagen->id.'.jpg';
            file_exists($f1)? unlink($f1);
            file_exists($f2)? unlink($f2);
        });
    }
}
