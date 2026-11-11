<?php
/**
 * Convierte informes.analisis_por_cargo del formato anterior (tabla HTML) al
 * JSON del ML (ver php/informes/analisis_por_cargo.php).
 *
 * Uso (desde la raiz del proyecto, con el entorno de Toba cargado):
 *   php deploy/convertir_analisis_por_cargo.php            simula y muestra el resumen
 *   php deploy/convertir_analisis_por_cargo.php --aplicar  convierte
 *
 * Toma la conexion de $TOBA_INSTALACION_DIR/bases.ini (o instalacion/bases.ini),
 * seccion [desarrollo catedras catedras]; --ini=RUTA y --seccion=NOMBRE la cambian.
 *
 * Con --aplicar, en una sola transaccion:
 *  - guarda el HTML original en <schema>.informes_apc_html_backup (no pisa un backup existente);
 *  - escribe el JSON solo si la columna sigue igual que cuando se leyo.
 * Es idempotente: los informes que ya tienen JSON se saltean.
 * Volver atras: UPDATE informes i SET analisis_por_cargo = b.analisis_por_cargo_html
 *               FROM informes_apc_html_backup b WHERE b.id_informe = i.id_informe;
 */

if (! class_exists('toba_error')) {
	class toba_error extends Exception {}
}
require __DIR__ . '/../php/informes/analisis_por_cargo.php';

$opciones = getopt('', array('aplicar', 'ini:', 'seccion:', 'detalle'));
$aplicar = isset($opciones['aplicar']);
$dir_instalacion = getenv('TOBA_INSTALACION_DIR') ?: (__DIR__ . '/../instalacion');
$ini = isset($opciones['ini']) ? $opciones['ini'] : $dir_instalacion . '/bases.ini';
$seccion = isset($opciones['seccion']) ? $opciones['seccion'] : 'desarrollo catedras catedras';

$bases = parse_ini_file($ini, true);
if (! isset($bases[$seccion])) {
	fwrite(STDERR, "No esta la seccion [$seccion] en $ini\n");
	exit(1);
}
$b = $bases[$seccion];
$dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s;options='--client_encoding=LATIN1'",
	$b['profile'], isset($b['puerto']) ? $b['puerto'] : 5432, $b['base']);
$db = new PDO($dsn, $b['usuario'], isset($b['clave']) ? $b['clave'] : '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$schema = isset($b['schema']) ? $b['schema'] : 'public';
$db->exec('SET search_path TO ' . $db->quote($schema));

$registros = $db->query("SELECT id_informe, estado_informe, analisis_por_cargo FROM informes
	WHERE analisis_por_cargo IS NOT NULL AND trim(analisis_por_cargo) <> '' ORDER BY id_informe")->fetchAll(PDO::FETCH_ASSOC);

$a_convertir = array();
$ya_json = 0; $filas_total = 0; $vacios = 0; $con_notas = 0;
$por_estado = array(); $notas_total = array(); $cargos = array();
foreach ($registros as $r) {
	if (analisis_por_cargo::filas($r['analisis_por_cargo']) !== null) {
		$ya_json++;
		continue;
	}
	$conv = analisis_por_cargo::desde_html($r['analisis_por_cargo']);
	$json = analisis_por_cargo::a_json($conv['filas']);
	$a_convertir[] = array('id' => $r['id_informe'], 'viejo' => $r['analisis_por_cargo'], 'json' => $json);
	$filas_total += count($conv['filas']);
	if ($json === null) {
		$vacios++;
	}
	if (! empty($conv['notas'])) {
		$con_notas++;
	}
	$por_estado[$r['estado_informe']] = (isset($por_estado[$r['estado_informe']]) ? $por_estado[$r['estado_informe']] : 0) + 1;
	foreach ($conv['notas'] as $n) {
		$notas_total[$n] = (isset($notas_total[$n]) ? $notas_total[$n] : 0) + 1;
		if (isset($opciones['detalle'])) {
			echo "  informe {$r['id_informe']}: $n\n";
		}
	}
	foreach ($conv['filas'] as $f) {
		$c = ($f['cargo'] ?: '?') . '/' . ($f['dedicacion'] ?: '?');
		$cargos[$c] = (isset($cargos[$c]) ? $cargos[$c] : 0) + 1;
	}
}

echo "Base: {$b['base']} (schema $schema)\n";
echo "Informes con analisis_por_cargo: " . count($registros) . "  | ya en JSON: $ya_json  | a convertir: " . count($a_convertir) . "\n";
echo "  por estado: " . json_encode($por_estado) . "\n";
echo "  filas de docentes: $filas_total  | tablas sin ninguna fila cargada (quedan NULL): $vacios\n";
echo "  informes con algo que fue a 'Otros': $con_notas\n";
ksort($cargos);
echo "Cargo/dedicacion resultantes: ";
foreach ($cargos as $k => $v) echo "$k=$v  ";
echo "\n";
arsort($notas_total);
echo "Lo no interpretado (queda con el texto original en 'Otros'):\n";
foreach ($notas_total as $n => $v) {
	echo "  $v x $n\n";
}

if (! $aplicar) {
	echo "\nSimulacion: no se cambio nada. Para convertir: --aplicar\n";
	exit(0);
}

$db->beginTransaction();
$db->exec("CREATE TABLE IF NOT EXISTS informes_apc_html_backup (
	id_informe integer PRIMARY KEY,
	analisis_por_cargo_html text NOT NULL,
	convertido_en timestamp NOT NULL DEFAULT now())");
$backup = $db->prepare('INSERT INTO informes_apc_html_backup (id_informe, analisis_por_cargo_html) VALUES (?, ?) ON CONFLICT (id_informe) DO NOTHING');
$update = $db->prepare('UPDATE informes SET analisis_por_cargo = ? WHERE id_informe = ? AND analisis_por_cargo = ?');
$convertidos = 0;
foreach ($a_convertir as $c) {
	$backup->execute(array($c['id'], $c['viejo']));
	$update->execute(array($c['json'], $c['id'], $c['viejo']));
	$convertidos += $update->rowCount();
}
$db->commit();
echo "\nConvertidos: $convertidos de " . count($a_convertir) . ". HTML original en $schema.informes_apc_html_backup\n";
