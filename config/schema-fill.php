<?php require __DIR__.'/../vendor/autoload.php';

$usuario = new Usuario;
$usuario->email = 'admin@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Lilita';
$usuario->apellido = 'Carrio';
$usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
$usuario->verificado = true;
$usuario->puntos = 0;
$usuario->suspendido = false;
$usuario->es_funcionario = false;
$usuario->es_jefe = false;
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim('admin@virtuago.ra')));
$usuario->save();

$patrulla = new Patrulla;
$patrulla->nombre = 'moderadores';
$patrulla->descripcion = 'Los moderadores.';
$patrulla->save();

$moderador = new Moderador;
$moderador->usuario()->associate($usuario);
$moderador->patrulla()->associate($patrulla);
$moderador->save();

$organismo = new Organismo;
$organismo->nombre = 'Presidente';
$organismo->descripcion = 'El presidente.';
$organismo->cupo = 1;
$organismo->save();

$funcionario = new Funcionario;
$funcionario->usuario()->associate($usuario);
$funcionario->organismo()->associate($organismo);
$funcionario->save();

$categoria = new Categoria;
$categoria->nombre = 'general';
$categoria->save();
