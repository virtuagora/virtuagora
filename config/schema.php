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
    $table->boolean('tiene_avatar');
    $table->string('token_verificacion');
    $table->boolean('verificado');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('funcionarios', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->dateTime('fecha_incio');
    $table->dateTime('fecha_fin')->nullable();
    $table->integer('usuario')->unsigned();

    $table->foreign('usuario')->references('id')->on('usuarios');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('ciudadanos', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->text('descripcion');
    $table->integer('prestigio');
    $table->boolean('suspendido');
    $table->dateTime('fecha_nacimiento')->nullable();
    $table->dateTime('fecha_certificado')->nullable();
    $table->integer('cargo_actual')->unsigned()->nullable();
    //$table->integer('partido_afiliado')->unsigned()->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    $table->foreign('cargo_actual')->references('id')->on('funcionarios');
    //$table->foreign('partido_afiliado')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('usuario_datos', function($table) {
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->string('lugar_nacimiento')->nullable();
    $table->string('lugar_recidencia')->nullable();
    $table->string('ocupacion')->nullable();

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

Capsule::schema()->create('organismos', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');
    $table->integer('cantidad_integrantes')->unsigned();
    $table->boolean('tiene_imagen');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('organismo_integrantes', function($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->boolean('activo');
    $table->integer('organismo')->unsigned();
    $table->integer('usuario')->unsigned();
    $table->integer('funcionario')->unsigned();

    $table->foreign('organismo')->references('id')->on('organismos');
    $table->foreign('usuario')->references('id')->on('usuarios');
    $table->foreign('funcionario')->references('id')->on('funcionarios');

    $table->timestamps();
});

Capsule::schema()->create('moderador_grupos', function($table) {
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
    $table->integer('patrulla')->unsigned();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    $table->foreign('patrulla')->references('id')->on('patrulla');

    $table->timestamps();
    $table->softDeletes();
});

echo 'holis';
