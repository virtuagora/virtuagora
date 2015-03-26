<?php require __DIR__.'/../vendor/autoload.php';

$usuario = new Usuario;
$usuario->email = 'admin@virtuago.ra';
$usuario->password = password_hash('12345678', PASSWORD_DEFAULT);
$usuario->nombre = 'Juan';
$usuario->apellido = 'Concejal';
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
$organismo->nombre = 'Concejo deliberante';
$organismo->descripcion = 'Honorable concejo deliberante de la ciudad.';
$organismo->cupo = 1;
$organismo->save();

$funcionario = new Funcionario;
$funcionario->usuario()->associate($usuario);
$funcionario->organismo()->associate($organismo);
$funcionario->save();

$categoria = new Categoria;
$categoria->nombre = 'general';
$categoria->save();

///

$documento = new Documento;
$documento->descripcion = 'Se presenta una propuesta para modificar la actual regulación de alquileres.';
$documento->ultima_version = 1;
$documento->save();
$docVersion = new VersionDocumento;
$docVersion->version = 1;
$docVersion->documento()->associate($documento);
$docVersion->save();

$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = '[u]Artículo 1 - PERÍODOS Y FORMA DE PAGO.[/u]';
$docParrafo->ubicacion = 0;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();
$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = 'El alquiler se paga en períodos mensuales. El período de pago corresponde al mes en que transcurre la ocupación del inmueble alquilado. La fecha de vencimiento para el pago del período correspondiente no podrá ser anterior al día diez (10) de cada mes. En caso de resolución anticipada, si la fecha de finalización del contrato no coincide con la fecha de cierre del período mensual de pago, se debe pagar la proporción que corresponda. Para ello se divide por treinta (30) el valor mensual del alquiler, multiplicándose el resultado por la cantidad de días a liquidar.';
$docParrafo->ubicacion = 1;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();

$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = '[u]Artículo 2 - MEDIOS DE PAGO.[/u]';
$docParrafo->ubicacion = 2;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();
$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = 'Los alquileres deben pagarse en moneda local, mediante depósito en la cuenta bancaria que la parte locadora indique al locatario mediante su individualización en el contrato. En el comprobante del depósito deberá hacerse consignar la causa del pago de manera tal que las entidades bancarias puedan informar, a pedido de la autoridad de aplicación, el listado de locaciones urbanas destinadas a vivienda, con detalle de los locadores.';
$docParrafo->ubicacion = 3;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();

$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = '[u]Artículo 3 - PRECIO.[/u]';
$docParrafo->ubicacion = 4;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();
$docParrafo = new ParrafoDocumento;
$docParrafo->cuerpo = 'El precio del alquiler mensual no podrá superar al 0,7% del valor inmobiliario de referencia del inmueble. A tal fin, la autoridad de aplicación establece y publica anualmente los valores inmobiliarios de referencia, previa tipificación de los inmuebles en base a criterios preestablecidos que contemplen por lo menos ubicación, antigüedad, aspectos constructivos, dimensiones, distribución, funcionalidad, servicios.';
$docParrafo->ubicacion = 5;
$docParrafo->version()->associate($docVersion);
$docParrafo->save();

$contenido = new Contenido;
$contenido->titulo = 'Modificación a regulación de alquileres';
$contenido->puntos = 0;
$contenido->categoria_id = 1;
$contenido->autor()->associate($usuario);
$contenido->contenible()->associate($documento);
$contenido->save();

$propuesta = new Propuesta;
$propuesta->cuerpo = <<<EOT
Este proyecto busca otorgar una reducción en la tarifa del colectivo durante el ciclo lectivo para los estudiantes y docentes con residencia en la Ciudad de Santa Fe, beneficio al que se conoce como &quot;Boleto Estudiantil&quot;.

La reducción corresponderá al sesenta por ciento (60%) de la tarifa para:
[ol]
[li]Alumno/as regulares de los niveles inicial, primario y secundario que asistan a establecimientos privados,[/li]
[li]Alumno/as regulares de los niveles superior, terciario y/o universitario de establecimientos públicos y,[/li]
[li]Docentes de los niveles inicial y primario oficialmente reconocidos.[/li]
[/ol]

Los pasajes serán gratuitos para los alumnos de establecimientos públicos de los niveles: inicial, primario y secundario.

Para la financiación del &quot;Boleto Estudiantil&quot; se creará un fondo público conformado por:
[ol]
[li]Los montos que el presupuesto general de la ciudad le asigne;[/li]
[li]Los aportes que en forma extraordinaria establezca el Poder Ejecutivo;[/li]
[li]Las donaciones y legados que se reciban de personas físicas o jurídicas, privadas o públicas, destinadas a este fondo;[/li]
[li]Los intereses devengados por la inversión de dinero correspondiente a este fondo.[/li]
[/ol]
EOT;
$propuesta->votos_favor = 0;
$propuesta->votos_contra = 0;
$propuesta->votos_neutro = 0;
$propuesta->save();
$contenido = new Contenido;
$contenido->titulo = 'Propuesta de Boleto Estudiantil';
$contenido->puntos = 0;
$contenido->categoria_id = 1;
$contenido->autor()->associate($usuario);
$contenido->contenible()->associate($propuesta);
$contenido->save();

$usuario = new Usuario;
$usuario->email = 'matuz9@gmail.com';
$usuario->password = password_hash('lalalala', PASSWORD_DEFAULT);
$usuario->nombre = 'Augusto';
$usuario->apellido = 'Mathurin';
$usuario->token_verificacion = bin2hex(openssl_random_pseudo_bytes(16));
$usuario->verificado = true;
$usuario->puntos = 0;
$usuario->suspendido = false;
$usuario->es_funcionario = false;
$usuario->es_jefe = false;
$usuario->img_tipo = 1;
$usuario->img_hash = md5(strtolower(trim('matuz9@gmail.com')));
$usuario->save();

$problematica = new Problematica;
$problematica->cuerpo = <<<EOT
El servicio de barrido público no está funcionando correctamente, pasan en horarios irregulares o incluso hay días en los que no aparecen.
Este es el reporte de los últimos días:
[ul]
[li][u]Lunes[/u]: no pasó.[/li]
[li][u]Martes[/u]: pasaron pero muy tarde.[/li]
[li][u]Miércoles[/u]: no pasó.[/li]
[li][u]Jueves[/u]: pasaron normalmente.[/li]
[/ul]
EOT;
$problematica->afectados_directos = 0;
$problematica->afectados_indirectos = 0;
$problematica->afectados_indiferentes = 0;
$problematica->save();
$contenido = new Contenido;
$contenido->titulo = 'Barrido público irregular';
$contenido->puntos = 0;
$contenido->categoria_id = 1;
$contenido->autor()->associate($usuario);
$contenido->contenible()->associate($problematica);
$contenido->save();

echo 'done!';
