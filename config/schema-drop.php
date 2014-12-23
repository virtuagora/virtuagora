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

Capsule::schema()->dropIfExists('comentarios');
echo '1 ';
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
Capsule::schema()->dropIfExists('imagenes');
echo '9 ';
Capsule::schema()->dropIfExists('contenidos');
echo '10 ';
Capsule::schema()->dropIfExists('categorias');
echo '11 ';
Capsule::schema()->dropIfExists('moderadores');
echo '12 ';
Capsule::schema()->dropIfExists('patrullas');
echo '13 ';
Capsule::schema()->dropIfExists('contactos');
echo '14 ';
Capsule::schema()->dropIfExists('usuario_datos');
echo '15 ';
Capsule::schema()->dropIfExists('funcionarios');
echo '16 ';
Capsule::schema()->dropIfExists('organismos');
echo '17 ';
Capsule::schema()->dropIfExists('partidos');
echo '18 ';
Capsule::schema()->dropIfExists('usuarios');
echo 'DONE!<br>';

echo 'deleting files... ';
superDelete('../public/img/partido');
echo 'DONE!<br>';
