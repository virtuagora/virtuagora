<?php
require __DIR__.'/../vendor/autoload.php';

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

Capsule::schema()->dropIfExists('usuarios');
Capsule::schema()->create('usuarios', function($table)
{
    $table->engine = 'InnoDB';

    $table->increments('id');
    $table->string('email')->unique();
    $table->string('password');
    $table->boolean('tiene_avatar');
    $table->string('token_verificacion');
    $table->boolean('verificado');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->dropIfExists('ciudadanos');
Capsule::schema()->create('ciudadanos', function($table)
{
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->string('nombre');
    $table->string('apellido');
    $table->text('descripcion');
    $table->integer('prestigio');
    $table->boolean('suspendido');
    $table->dateTime('fecha_nacimiento')->nullable();
    $table->dateTime('fecha_certificado')->nullable();
    //$table->integer('cargo_actual')->unsigned()->nullable();
    //$table->integer('partido_afiliado')->unsigned()->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    //$table->foreign('cargo_actual')->references('id')->on('funcionarios');
    //$table->foreign('partido_afiliado')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->dropIfExists('usuario_datos');
Capsule::schema()->create('usuario_datos', function($table)
{
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->string('lugar_nacimiento')->nullable();
    $table->string('lugar_recidencia')->nullable();
    $table->string('ocupacion')->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    //$table->foreign('cargo_actual')->references('id')->on('funcionarios');
    //$table->foreign('partido_afiliado')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->dropIfExists('usuario_contacto');
Capsule::schema()->create('usuario_contacto', function($table)
{
    $table->engine = 'InnoDB';

    $table->integer('id')->unsigned();
    $table->string('email')->nullable();
    $table->string('telefono')->nullable();
    $table->string('url')->nullable();

    $table->primary('id');
    $table->foreign('id')->references('id')->on('usuarios');
    //$table->foreign('cargo_actual')->references('id')->on('funcionarios');
    //$table->foreign('partido_afiliado')->references('id')->on('partidos');

    $table->timestamps();
    $table->softDeletes();
});

echo 'holis';
