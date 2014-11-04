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
    $table->string('fundador');
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

Capsule::schema()->create('contenidos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('contenible');
    $table->string('titulo');
    $table->integer('puntos')->unsigned();
    $table->integer('autor_id')->unsigned();
    $table->integer('partido_id')->unsigned()->nullable();

    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('tags', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
});

Capsule::schema()->create('contenido_tag', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('contenido_id')->unsigned();
    $table->integer('tag_id')->unsigned();

    $table->foreign('contenido_id')->references('id')->on('contenidos')->onDelete('cascade');
    $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
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

Capsule::schema()->create('propuesta_usuario', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('postura');
    $table->boolean('publico');
    $table->integer('propuesta_id')->unsigned();
    $table->integer('usuario_id')->unsigned();

    $table->primary(array('propuesta_id', 'usuario_id'));
    $table->foreign('propuesta_id')->references('id')->on('propuestas')->onDelete('cascade');

    $table->timestamps();
});

Capsule::schema()->create('problematica', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('votos_afectados')->unsigned();
    $table->integer('votos_soporte')->unsigned();
    $table->integer('votos_adversos')->unsigned();

    $table->foreign('id')->references('id')->on('contenidos')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

echo 'holis';
