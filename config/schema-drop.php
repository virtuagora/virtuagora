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

Capsule::schema()->dropIfExists('comentarios');
echo '1 ';
Capsule::schema()->dropIfExists('problematica_votos');
echo '2 ';
Capsule::schema()->dropIfExists('problematicas');
echo '3 ';
Capsule::schema()->dropIfExists('propuesta_usuario');
echo '4 ';
Capsule::schema()->dropIfExists('propuestas');
echo '5 ';
Capsule::schema()->dropIfExists('imagenes');
echo '6 ';
Capsule::schema()->dropIfExists('contenidos');
echo '7 ';
Capsule::schema()->dropIfExists('categorias');
echo '8 ';
Capsule::schema()->dropIfExists('moderadores');
echo '9 ';
Capsule::schema()->dropIfExists('patrullas');
echo '10 ';
Capsule::schema()->dropIfExists('contactos');
echo '11 ';
Capsule::schema()->dropIfExists('usuario_datos');
echo '12 ';
Capsule::schema()->dropIfExists('funcionarios');
echo '13 ';
Capsule::schema()->dropIfExists('organismos');
echo '14 ';
Capsule::schema()->dropIfExists('partidos');
echo '15 ';
Capsule::schema()->dropIfExists('usuarios');
echo 'DONE! ';
