<?php

use Illuminate\Database\Capsule\Manager as Capsule;

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
    $table->string('huella')->nullable();
    $table->integer('puntos')->default(0);
    $table->string('advertencia')->nullable();
    $table->boolean('suspendido')->default(0);
    $table->boolean('es_funcionario')->default(0);
    $table->boolean('es_jefe')->default(0);
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
    $table->text('descripcion');
    $table->string('huella')->nullable();
    $table->string('fundador')->nullable();
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
    $table->string('huella')->nullable();
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
    $table->string('subclase');
    $table->morphs('objeto');
    $table->integer('poder_id')->unsigned();
    $table->integer('actor_id')->unsigned();
    $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
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
    $table->string('huella')->nullable();
    $table->integer('puntos')->unsigned()->default(0);
    $table->integer('impulsor_id')->unsigned()->nullable();
    $table->integer('referido_id')->unsigned()->nullable();
    $table->integer('categoria_id')->unsigned();
    $table->integer('autor_id')->unsigned();
    $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('tags', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->string('nombre');
    $table->string('huella')->nullable();
    $table->integer('menciones')->unsigned()->default(0);
    $table->timestamps();
});
Capsule::schema()->create('taggables', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->morphs('taggable');
    $table->integer('tag_id')->unsigned();
    $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
});
Capsule::schema()->create('propuestas', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('votos_favor')->unsigned()->default(0);
    $table->integer('votos_contra')->unsigned()->default(0);
    $table->integer('votos_neutro')->unsigned()->default(0);
    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('propuesta_votos', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->integer('postura');
    $table->boolean('publico');
    $table->integer('usuario_id')->unsigned();
    $table->integer('propuesta_id')->unsigned();
    $table->foreign('propuesta_id')->references('id')->on('propuestas')->onDelete('cascade');
    $table->timestamps();
});
Capsule::schema()->create('problematicas', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->text('cuerpo');
    $table->integer('afectados_directos')->unsigned()->default(0);
    $table->integer('afectados_indirectos')->unsigned()->default(0);
    $table->integer('afectados_indiferentes')->unsigned()->default(0);
    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('problematica_votos', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->integer('postura')->unsigned();
    $table->integer('usuario_id')->unsigned();
    $table->integer('problematica_id')->unsigned();
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
    $table->string('lugar');
    $table->timestamp('fecha');
    $table->timestamps();
    $table->softDeletes();
});
Capsule::schema()->create('evento_usuario', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->boolean('presente');
    $table->boolean('publico');
    $table->integer('usuario_id')->unsigned();
    $table->integer('evento_id')->unsigned();
    $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
    $table->timestamps();
});
Capsule::schema()->create('comentarios', function($table) {
    $table->engine = 'InnoDB';
    $table->increments('id');
    $table->morphs('comentable');
    $table->text('cuerpo');
    $table->integer('votos')->default(0);
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
$ajuste = new Ajuste;
$ajuste->key = 'tos';
$ajuste->value_type = 'txt';
$ajuste->value = 'Términos y condiciones de uso.';
$ajuste->description = 'Términos y condiciones para el uso de la plataforma.';
$ajuste->save();
$categoria = new Categoria;
$categoria->nombre = 'General';
$categoria->save();
$usuario = new Usuario;
$usuario->email = 'admin@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Administrador';
$usuario->apellido = 'Test';
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim($usuario->email)));
$usuario->es_funcionario = 1;
$patrulla = new Patrulla;
$patrulla->nombre = 'Aministrador';
$patrulla->descripcion = 'Admnistrador que instaló la plataforma.';
$patrulla->save();
$poderes = [
    ['nombre' => 'Moderar', 'descripcion' => 'Moderar en la plataforma.'],
    ['nombre' => 'Configurar plataforma', 'descripcion' => 'Configurar parámetros de Virtugora.'],
    ['nombre' => 'Administrar organismos', 'descripcion' => 'Definir los organimos existentes.'],
    ['nombre' => 'Administrar funcionarios', 'descripcion' => 'Asignar los funcionarios a sus respectivos organismos.'],
    ['nombre' => 'Administrar patrullas', 'descripcion' => 'Definir los distintos grupos de moderación.'],
    ['nombre' => 'Administrar moderadores', 'descripcion' => 'Asignar los usuarios que serán moderadores.'],
    ['nombre' => 'Verificar ciudadanos', 'descripcion' => 'Registrar como verificados a usuarios que lo demuestren.'],
];
Poder::insert($poderes);
$patrulla->poderes()->attach([1,2,3,4,5,6,7]);
$usuario->patrulla()->associate($patrulla);
$usuario->save();

$organis = new Organismo;
$organis->nombre = 'Organismo Test';
$organis->descripcion = 'Organismo creado para hacer pruebas.';
$organis->cupo = 3;
$organis->save();

$funcion = new Funcionario;
$funcion->usuario()->associate($usuario);
$funcion->organismo()->associate($organis);
$funcion->save();

$organis = new Organismo;
$organis->nombre = 'Organismo Borrable';
$organis->descripcion = 'Organismo creado para probar borrarlo.';
$organis->cupo = 5;
$organis->save();

$partido = new Partido;
$partido->nombre = 'Partido Test';
$partido->acronimo = 'PT';
$partido->descripcion = 'Partido creado para realizar pruebas';
$partido->creador()->associate($usuario);
$partido->save();
$contact = new Contacto;
$contact->contactable()->associate($partido);
$contact->save();
$usuario->es_jefe = 1;
$usuario->partido_id = 1;
$usuario->save();

$problem = new Problematica;
$problem->cuerpo = 'Problemática creada para hacer pruebas.';
$problem->save();
$conteni = new Contenido;
$conteni->titulo = 'Primer Problemática';
$conteni->categoria_id = 1;
$conteni->autor()->associate($usuario);
$conteni->contenible()->associate($problem);
$conteni->save();

$propues = new Propuesta;
$propues->cuerpo = 'Propuesta creada para hacer pruebas.';
$propues->save();
$conteni = new Contenido;
$conteni->titulo = 'Primer Propuesta';
$conteni->categoria_id = 1;
$conteni->autor()->associate($usuario);
$conteni->contenible()->associate($propues);
$conteni->save();

$documen = new Documento;
$documen->descripcion = 'Esta es una descripcion.';
$documen->ultima_version = 1;
$documen->save();
$docVers = new VersionDocumento;
$docVers->version = 1;
$docVers->documento()->associate($documen);
$docVers->save();
$docParr = new ParrafoDocumento;
$docParr->cuerpo = 'Documento creado para hacer pruebas.';
$docParr->ubicacion = 0;
$docParr->version()->associate($docVers);
$docParr->save();
$conteni = new Contenido;
$conteni->titulo = 'Primer Documento';
$conteni->categoria_id = 1;
$conteni->autor()->associate($usuario);
$conteni->contenible()->associate($documen);
$conteni->save();

$eventoo = new Evento;
$eventoo->cuerpo = 'Evento creada para hacer pruebas.';
$eventoo->lugar = 'Calle Test 123';
$eventoo->fecha = Carbon\Carbon::parse('2035-07-25 12:00:00');
$eventoo->save();
$conteni = new Contenido;
$conteni->titulo = 'Primer Evento';
$conteni->categoria_id = 1;
$conteni->autor()->associate($usuario);
$conteni->contenible()->associate($eventoo);
$conteni->save();

$novedad = new Novedad;
$novedad->cuerpo = 'Novedad creada para hacer pruebas.';
$novedad->save();
$conteni = new Contenido;
$conteni->titulo = 'Primer Novedad';
$conteni->categoria_id = 1;
$conteni->autor()->associate($usuario);
$conteni->contenible()->associate($novedad);
$conteni->save();

$usuario = new Usuario;
$usuario->email = 'user@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Usuario';
$usuario->apellido = 'Test';
$usuario->puntos = 20;
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim($usuario->email)));
$usuario->save();

$usuario = new Usuario;
$usuario->email = 'delete@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Borrable';
$usuario->apellido = 'Test';
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim($usuario->email)));
$usuario->save();

$usuario = new Usuario;
$usuario->email = 'extra@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Extra';
$usuario->apellido = 'Test';
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim($usuario->email)));
$usuario->partido()->associate($partido);
$usuario->save();

$comentario = new Comentario;
$comentario->cuerpo = 'Este es un comentario de test.';
$comentario->autor()->associate($usuario);
$comentario->comentable()->associate($problem);
$comentario->save();

$patrulla = new Patrulla;
$patrulla->nombre = 'Patrulla Test';
$patrulla->descripcion = 'Patrulla para testear.';
$patrulla->save();

$usuario->patrulla()->associate($patrulla);
$usuario->save();

$patrulla = new Patrulla;
$patrulla->nombre = 'Patrulla Borrable';
$patrulla->descripcion = 'Patrulla para testear borrarla.';
$patrulla->save();
