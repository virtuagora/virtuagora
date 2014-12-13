<?php require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(array(
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'virtuagora',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
));
$capsule->setAsGlobal();

Capsule::schema()->create('usuarios', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('nombre');
    $table->string('apellido');
    $table->string('token_verificacion');
    $table->boolean('verificado');
    $table->integer('img_tipo')->unsigned();
    $table->string('img_hash');
    $table->integer('puntos');
    $table->boolean('suspendido');
    $table->boolean('es_funcionario');
    $table->boolean('es_jefe');
    $table->string('dni')->nullable();
    $table->dateTime('fecha_certificado')->nullable();
    $table->integer('partido_id')->unsigned()->nullable();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('partidos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->string('acronimo');
    $table->string('fundador')->nullable();
    $table->text('descripcion');
    $table->dateTime('fecha_fundacion')->nullable();
    $table->integer('creador_id')->unsigned();

    $table->unique('nombre');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('organismos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');
    $table->integer('cupo')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('funcionarios', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->integer('usuario_id')->unsigned();
    $table->integer('organismo_id')->unsigned();

    $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('usuario_datos', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->dateTime('fecha_nacimiento')->nullable();
    $table->string('lugar_nacimiento')->nullable();
    $table->string('lugar_recidencia')->nullable();
    $table->string('ocupacion')->nullable();
    $table->text('descripcion')->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('contactos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('contactable');
    $table->string('email')->nullable();
    $table->string('telefono')->nullable();
    $table->string('web')->nullable();

    $table->timestamps();
});

Capsule::schema()->create('patrullas', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');

    $table->timestamps();
});

Capsule::schema()->create('moderadores', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->integer('patrulla_id')->unsigned();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->foreign('patrulla_id')->references('id')->on('patrullas')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('categorias', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('contenidos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('contenible');
    $table->string('titulo');
    $table->integer('puntos')->unsigned();
    $table->integer('autor_id')->unsigned();
    $table->integer('categoria_id')->unsigned();
    $table->integer('partido_id')->unsigned()->nullable();

    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('restrict');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('imagenes', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('imagenable');
    $table->string('titulo');
    $table->string('extension');
    $table->integer('autor_id')->unsigned();

    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('propuestas', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('votos_favor')->unsigned();
    $table->integer('votos_contra')->unsigned();
    $table->integer('votos_neutro')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('propuesta_votos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->integer('postura');
    $table->boolean('publico');
    $table->integer('propuesta_id')->unsigned();
    $table->integer('usuario_id')->unsigned();

    $table->foreign('propuesta_id')->references('id')->on('propuestas')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('problematicas', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('afectados_directos')->unsigned();
    $table->integer('afectados_indirectos')->unsigned();
    $table->integer('afectados_indiferentes')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('problematica_votos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->integer('postura')->unsigned();
    $table->integer('problematica_id')->unsigned();
    $table->integer('usuario_id')->unsigned();

    $table->foreign('problematica_id')->references('id')->on('problematicas')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('documentos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('descripcion');
    $table->integer('ultima_version')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('documento_versiones', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->integer('version')->unsigned();
    $table->integer('documento_id')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('documento_parrafos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('ubicacion')->unsigned();
    $table->integer('version_id')->unsigned();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('comentarios', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('comentable');
    $table->text('cuerpo');
    $table->integer('votos');
    $table->integer('autor_id')->unsigned();

    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

echo 'holis';
