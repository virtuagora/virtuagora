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

Capsule::schema()->create('ajustes', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('key')->unique();
    $table->string('value_type');
    $table->integer('int_value')->nullable();
    $table->string('str_value')->nullable();
    $table->text('txt_value')->nullable();
    $table->string('description');
    $table->timestamps();
});

Capsule::schema()->create('usuarios', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('nombre');
    $table->string('apellido');
    $table->integer('img_tipo')->unsigned();
    $table->string('img_hash');
    $table->integer('puntos');
    $table->string('advertencia')->nullable();
    $table->boolean('suspendido');
    $table->boolean('es_funcionario');
    $table->boolean('es_jefe');
    $table->string('dni')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->timestamp('fin_advertencia')->nullable();
    $table->timestamp('fin_suspension')->nullable();
    $table->integer('partido_id')->unsigned()->nullable();
    $table->integer('patrulla_id')->unsigned()->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('preusuarios', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('nombre');
    $table->string('apellido');
    $table->string('emailed_token');
    $table->timestamps();
});

Capsule::schema()->create('partidos', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('nombre')->unique();
    $table->string('acronimo');
    $table->string('fundador')->nullable();
    $table->text('descripcion');
    $table->date('fecha_fundacion')->nullable();
    $table->integer('creador_id')->unsigned();
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
    $table->integer('id')->unsigned()->primary();
    $table->date('fecha_nacimiento')->nullable();
    $table->string('lugar_nacimiento')->nullable();
    $table->string('lugar_recidencia')->nullable();
    $table->string('ocupacion')->nullable();
    $table->text('descripcion')->nullable();
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

Capsule::schema()->create('acciones', function($table) {
    $table->engine = 'InnoDB';
    $table->string('id', 10)->primary();
    $table->string('nombre');
});

Capsule::schema()->create('userlogs', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->morphs('objeto');
    $table->string('accion_id', 10);
    $table->integer('actor_id')->unsigned();
    $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->timestamps();
});

Capsule::schema()->create('notificaciones', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->morphs('notificable');
    $table->integer('usuario_id')->unsigned();
    $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('patrullas', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('nombre');
    $table->text('descripcion');
    $table->timestamps();
});

Capsule::schema()->create('poderes', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('nombre');
    $table->string('descripcion');
});

Capsule::schema()->create('patrulla_poder', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->integer('patrulla_id')->unsigned();
    $table->integer('poder_id')->unsigned();
    $table->foreign('patrulla_id')->references('id')->on('patrullas')->onDelete('cascade');
    $table->foreign('poder_id')->references('id')->on('poderes')->onDelete('cascade');
});

Capsule::schema()->create('adminlogs', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('descripcion');
    $table->morphs('objeto');
    $table->integer('actor_id')->unsigned();
    $table->integer('accion_id')->unsigned();
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
    $table->integer('impulsor_id')->unsigned();
    $table->integer('autor_id')->unsigned();
    $table->integer('categoria_id')->unsigned();
    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('restrict');
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
    $table->increments('id');
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

Capsule::schema()->create('novedades', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->text('cuerpo');
    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('eventos', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->text('cuerpo');
    $table->timestamp('fecha');
    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('evento_asistencias', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->boolean('presente');
    $table->boolean('publico');
    $table->integer('evento_id')->unsigned();
    $table->integer('usuario_id')->unsigned();
    $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
    $table->timestamps();
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

Capsule::schema()->create('comentario_votos', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->integer('valor');
    $table->integer('usuario_id')->unsigned();
    $table->integer('comentario_id')->unsigned();
    $table->foreign('comentario_id')->references('id')->on('comentarios')->onDelete('cascade');
    $table->timestamps();
});

echo 'holis';
