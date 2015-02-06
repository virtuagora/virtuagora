<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Documento extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'documento';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'descripcion', 'ultima_version');

    public function contenido() {
        return $this->morphOne('Contenido', 'contenible');
    }

    public function versiones() {
        return $this->hasMany('VersionDocumento');
    }

    public function parrafos() {
        return $this->hasManyThrough('ParrafoDocumento', 'VersionDocumento', 'documento_id', 'version_id');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($documento) {
            foreach ($documento->parrafos as $parrafo) {
                $CommentIds = $parrafo->comentarios()->lists('id');
                if ($CommentIds) {
                    $AnswerIds = Comentario::where('comentable_type', 'Comentario')->whereIn('comentable_id', $CommentIds)->lists('id');
                    if ($AnswerIds) {
                        VotoComentario::whereIn('comentario_id', $AnswerIds)->delete();
                        Comentario::whereIn('id', $AnswerIds)->delete();
                    }
                    VotoComentario::whereIn('comentario_id', $CommentIds)->delete();
                    $parrafo->comentarios()->delete();
                }
            }
            $documento->parrafos()->delete();
            $documento->versiones()->delete();
            $documento->contenido->delete();
            return true;
        });
    }

}
