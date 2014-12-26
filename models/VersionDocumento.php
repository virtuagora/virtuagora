<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VersionDocumento extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'documento_versiones';
    protected $dates = ['deleted_at'];
    protected $visible = array('id', 'version', 'parrafos');
    protected $with = array('parrafos');

    public function documento() {
        return $this->belongsTo('Documento');
    }

    public function parrafos() {
        return $this->hasMany('ParrafoDocumento', 'version_id');
    }

}
