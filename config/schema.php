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
    $table->boolean('imagen');
    $table->string('token_verificacion');
    $table->boolean('verificado');
    $table->integer('puntos');
    $table->boolean('suspendido');
    $table->boolean('es_funcionario');
    $table->string('dni')->nullable();
    $table->dateTime('fecha_nacimiento')->nullable();
    $table->dateTime('fecha_certificado')->nullable();
    //$table->integer('partido_afiliado')->unsigned()->nullable();

    //$table->foreign('partido_afiliado')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('organismos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');
    $table->integer('cupo')->unsigned();
    $table->boolean('imagen');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('funcionarios', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->integer('usuario_id')->unsigned();
    $table->integer('organismo_id')->unsigned();

    $table->foreign('usuario_id')->references('id')->on('usuarios');
    $table->foreign('organismo_id')->references('id')->on('organismos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('usuario_datos', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->string('lugar_nacimiento')->nullable();
    $table->string('lugar_recidencia')->nullable();
    $table->string('ocupacion')->nullable();
    $table->text('descripcion')->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('contactos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('contactable');
    $table->string('email')->nullable();
    $table->string('telefono')->nullable();
    $table->string('web')->nullable();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('patrullas', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('moderadores', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->integer('patrulla_id')->unsigned();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    $table->foreign('patrulla_id')->references('id')->on('patrulla');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('contenidos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->morphs('contenido');
    $table->string('titulo');
    $table->integer('puntos')->unsigned();
    $table->integer('usuario_id')->unsigned();
    //$table->integer('partido')->unsigned()->nullable();

    $table->foreign('usuario_id')->references('id')->on('usuarios');
    //$table->foreign('partido')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('propuestas', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->text('contenido');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('propuesta_usuario', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('tipo');
    $table->boolean('publico');
    $table->integer('propuesta_id')->unsigned();
    $table->integer('usuario_id')->unsigned();

    $table->primary(array('propuesta_id', 'usuario_id'));
    $table->foreign('propuesta_id')->references('id')->on('propuestas');
    $table->foreign('usuario_id')->references('id')->on('usuarios');

    $table->timestamps();
});

echo 'holis';
