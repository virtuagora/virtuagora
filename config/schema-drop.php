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

function superDelete($path) {
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            superDelete(realpath($path) . '/' . $file);
        }
        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }
}

Capsule::schema()->dropIfExists('comentario_votos');
echo '0 ';
Capsule::schema()->dropIfExists('comentarios');
echo '1 ';
Capsule::schema()->dropIfExists('evento_usuario');
echo '1.1 ';
Capsule::schema()->dropIfExists('eventos');
echo '1.2 ';
Capsule::schema()->dropIfExists('novedades');
echo '1.3 ';
Capsule::schema()->dropIfExists('documento_parrafos');
echo '2 ';
Capsule::schema()->dropIfExists('documento_versiones');
echo '3 ';
Capsule::schema()->dropIfExists('documentos');
echo '4 ';
Capsule::schema()->dropIfExists('problematica_votos');
echo '5 ';
Capsule::schema()->dropIfExists('problematicas');
echo '6 ';
Capsule::schema()->dropIfExists('propuesta_votos');
echo '7 ';
Capsule::schema()->dropIfExists('propuestas');
echo '8 ';
Capsule::schema()->dropIfExists('taggables');
echo '9 ';
Capsule::schema()->dropIfExists('tags');
echo '9.1 ';
Capsule::schema()->dropIfExists('contenidos');
echo '10 ';
Capsule::schema()->dropIfExists('categorias');
echo '11 ';
Capsule::schema()->dropIfExists('patrulla_poder');
echo '11.1 ';
Capsule::schema()->dropIfExists('adminlogs');
echo '11.2 ';
Capsule::schema()->dropIfExists('poderes');
echo '12 ';
Capsule::schema()->dropIfExists('patrulla_poder');
echo '12.1 ';
Capsule::schema()->dropIfExists('patrullas');
echo '13 ';
Capsule::schema()->dropIfExists('notificaciones');
echo '13.1 ';
Capsule::schema()->dropIfExists('userlogs');
echo '13.2 ';
Capsule::schema()->dropIfExists('acciones');
echo '14 ';
Capsule::schema()->dropIfExists('contactos');
echo '15 ';
Capsule::schema()->dropIfExists('funcionarios');
echo '16 ';
Capsule::schema()->dropIfExists('organismos');
echo '17 ';
Capsule::schema()->dropIfExists('partidos');
echo '18 ';
Capsule::schema()->dropIfExists('preusuarios');
echo '19 ';
Capsule::schema()->dropIfExists('usuarios');
echo '20 ';
Capsule::schema()->dropIfExists('ajustes');
echo 'DONE!<br>';

echo 'deleting files... ';
superDelete('../public/img');
echo 'DONE!<br>';
