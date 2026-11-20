<?php
/**
 * Alta y modificacion de materias (huayca.materias) desde deptos, por
 * POST /rest/materias-alta (php/rest/recurso_materias_alta.php).
 *
 * Secretaria sube en deptos (/programas/materias) un CSV con las columnas de
 * la tabla (sin id_materia) y deptos manda las filas. Por fila:
 *
 *   - La materia es la misma si coinciden cod_guarani, plan_guarani y
 *     version_guarani (los tres son obligatorios).
 *   - Ninguna con esa clave: alta.
 *   - Una: modificacion. Solo los campos con valor en la fila; una celda
 *     vacia deja lo que hay (para borrar un campo, en Catedras). Si no cambia
 *     nada, "sin cambios". Se puede pedir que solo se modifiquen algunos
 *     campos, para todas o para cada fila: lo demas se informa y no se toca.
 *   - Mas de una (ya hay materias repetidas en la tabla): no se toca, va a
 *     problemas, para resolverlo a mano en Catedras.
 *   - La misma clave dos veces en el archivo: no se aplica ninguna de las dos.
 *
 * ano_plan es entero en la base: "4 y 5" se guarda como 4 (el primer numero)
 * y va un aviso. Los textos que no entran en su columna son un problema de
 * la fila (no se recortan). Departamento y carrera (ver $CONOCIDAS) que
 * coinciden con los de otras materias salvo tildes o mayusculas toman la
 * escritura de la tabla, con aviso: si no, el departamento quedaria partido.
 *
 * Todo en una transaccion. Con simular solo se planifica: no se escribe nada.
 * Al confirmar se vuelve a planificar contra la base, asi que mandar lo mismo
 * dos veces no cambia nada la segunda.
 *
 * Los textos van en LATIN1, como la base (la API REST ya convierte el JSON).
 * Archivo en ASCII.
 */
class catedras_materias
{
	const TABLA = 'materias';

	// Columna => largo maximo (null = text; 'int' = ano_plan).
	static $COLUMNAS = array(
		'nombre_carrera' => 150, 'nombre_materia' => null, 'ano_plan' => 'int',
		'periodo_plan' => 50, 'horas_totales' => 50, 'horas_semanales' => 50,
		'depto_principal' => 150, 'depto' => 150, 'area' => 150, 'orientacion' => 150,
		'contenidos_minimos' => null, 'correlativas_para_cursar' => null,
		'correlativas_para_aprobar' => null, 'competencias' => null,
		'optativa' => 10, 'trayecto' => 150, 'cod_carrera' => 50, 'plan_guarani' => 50,
		'version_guarani' => 50, 'plan_mocovi' => 50, 'plan_ordenanzas' => null,
		'cod_guarani' => 50, 'observaciones' => null,
	);

	static $CLAVE = array('cod_guarani', 'plan_guarani', 'version_guarani');

	// Sin estos no se da de alta (en una modificacion pueden venir vacios).
	static $OBLIGATORIOS_ALTA = array('nombre_materia', 'nombre_carrera', 'cod_carrera', 'plan_mocovi');

	static function db()
	{
		return toba::db('catedras');
	}

	/**
	 * Las filas tal como llegan ({columna: texto}) => cada una con 'linea'
	 * (la del archivo, si viene, o la posicion), 'valores' (solo las columnas
	 * con valor, ano_plan como entero), 'avisos' y 'problemas'.
	 */
	static function normalizar($filas)
	{
		$salida = array();
		foreach (array_values($filas) as $i => $f) {
			$linea = (isset($f['linea']) && ctype_digit((string) $f['linea'])) ? (int) $f['linea'] : $i + 1;
			$r = array('linea' => $linea, 'valores' => array(), 'avisos' => array(), 'problemas' => array());
			foreach (self::$COLUMNAS as $c => $largo) {
				$v = (isset($f[$c]) && !is_array($f[$c])) ? trim((string) $f[$c]) : '';
				if ($v === '') {
					continue;
				}
				if ($largo === 'int') {
					if (!preg_match('/[0-9]+/', $v, $m)) {
						$r['problemas'][] = "$c: \"$v\" no tiene un numero.";
						continue;
					}
					if ($m[0] !== $v) {
						$r['avisos'][] = "$c: se guarda " . (int) $m[0] . " (decia \"$v\").";
					}
					$v = (int) $m[0];
				} elseif ($largo !== null && strlen($v) > $largo) {
					$r['problemas'][] = "$c: tiene " . strlen($v) . " caracteres y entran $largo.";
					continue;
				}
				$r['valores'][$c] = $v;
			}
			foreach (self::$CLAVE as $c) {
				if (!isset($r['valores'][$c])) {
					$r['problemas'][] = "Falta $c (la materia se identifica por cod_guarani, plan_guarani y version_guarani).";
				}
			}
			$salida[] = $r;
		}
		return $salida;
	}

	static function clave($valores)
	{
		$k = array();
		foreach (self::$CLAVE as $c) {
			$k[] = isset($valores[$c]) ? strtoupper(trim((string) $valores[$c])) : '';
		}
		return implode('|', $k);
	}

	/** Las materias que ya estan, por clave: {clave: [fila, ...]}. */
	static function existentes($db)
	{
		$por_clave = array();
		$sql = 'SELECT * FROM ' . self::TABLA . ' ORDER BY id_materia';
		foreach ($db->consultar($sql) as $m) {
			$por_clave[self::clave($m)][] = $m;
		}
		return $por_clave;
	}

	// Columnas por las que se agrupa (departamentos, carreras): un valor que no
	// esta en ninguna otra materia es casi siempre un error de tipeo o de tildes.
	static $CONOCIDAS = array('depto_principal', 'depto', 'area', 'orientacion', 'cod_carrera', 'nombre_carrera');

	/** Sin tildes, en mayusculas y con un espacio entre palabras (LATIN1). */
	static function plano($s)
	{
		$con = chr(193) . chr(201) . chr(205) . chr(211) . chr(218) . chr(220)
			. chr(225) . chr(233) . chr(237) . chr(243) . chr(250) . chr(252);
		return strtoupper(preg_replace('/\s+/', ' ', trim(strtr((string) $s, $con, 'AEIOUUaeiouu'))));
	}

	/**
	 * {columna: {plano: valor}} de lo que ya hay en la tabla (el valor mas
	 * usado para cada forma plana).
	 */
	static function conocidos($existentes)
	{
		$cuenta = array_fill_keys(self::$CONOCIDAS, array());
		foreach ($existentes as $lista) {
			foreach ($lista as $m) {
				foreach (self::$CONOCIDAS as $c) {
					if ($m[$c] !== null && trim($m[$c]) !== '') {
						$v = trim($m[$c]);
						$cuenta[$c][self::plano($v)][$v] = ($cuenta[$c][self::plano($v)][$v] ?? 0) + 1;
					}
				}
			}
		}
		$r = array();
		foreach ($cuenta as $c => $por_plano) {
			$r[$c] = array();
			foreach ($por_plano as $p => $valores) {
				arsort($valores);
				$r[$c][$p] = key($valores);
			}
		}
		return $r;
	}

	/**
	 * Los valores de $CONOCIDAS que coinciden con uno de la tabla salvo
	 * tildes, mayusculas o espacios toman el de la tabla (para no partir un
	 * departamento en dos); los que no coinciden con ninguno van con aviso.
	 */
	static function ajustar_conocidos($valores, &$avisos, $conocidos)
	{
		foreach (self::$CONOCIDAS as $c) {
			if (!isset($valores[$c])) {
				continue;
			}
			$v = $valores[$c];
			$p = self::plano($v);
			if (!isset($conocidos[$c][$p])) {
				$avisos[] = "$c: \"$v\" no lo tiene ninguna otra materia (revisar si esta bien escrito).";
			} elseif ($conocidos[$c][$p] !== $v) {
				$valores[$c] = $conocidos[$c][$p];
				$avisos[] = "$c: se toma \"" . $conocidos[$c][$p] . "\", como en las demas materias (decia \"$v\").";
			}
		}
		return $valores;
	}

	static function igual($columna, $actual, $nuevo)
	{
		if ($actual === null) {
			return false;
		}
		if (self::$COLUMNAS[$columna] === 'int') {
			return (int) $actual === (int) $nuevo;
		}
		return trim((string) $actual) === (string) $nuevo;
	}

	/**
	 * array('altas' => [...], 'modificaciones' => [...], 'sin_cambios' => [...],
	 * 'problemas' => [...]). Cada una con linea, clave, nombre_materia,
	 * cod_carrera y avisos; las modificaciones y sin cambios con id_materia;
	 * las modificaciones con 'cambios' [{campo, antes, despues}]; las altas con
	 * 'valores'; los problemas con 'motivos'. No escribe nada.
	 *
	 * $campos: las columnas que se pueden modificar (null = todas).
	 * $por_linea: {linea: [columnas]}, lo elegido para cada fila; le gana a
	 * $campos en esa fila. Lo que cambiaria en las demas columnas va en
	 * 'no_se_tocan' de cada fila (mismo formato que 'cambios') y no se guarda;
	 * una materia con cambios solo ahi queda en sin_cambios. Las altas llevan
	 * todos sus campos. 'campos_con_cambios': las columnas que cambian en
	 * alguna materia, elegidas o no.
	 */
	static function planificar($db, $filas, $campos = null, $por_linea = null)
	{
		$r = array('altas' => array(), 'modificaciones' => array(), 'sin_cambios' => array(), 'problemas' => array(),
			'campos_con_cambios' => array());
		$con_cambios = array();
		$existentes = self::existentes($db);
		$conocidos = self::conocidos($existentes);
		$veces = array();
		foreach ($filas as $f) {
			if (!$f['problemas']) {
				$k = self::clave($f['valores']);
				$veces[$k] = ($veces[$k] ?? 0) + 1;
			}
		}
		foreach ($filas as $f) {
			$avisos = $f['avisos'];
			$v = self::ajustar_conocidos($f['valores'], $avisos, $conocidos);
			$base = array('linea' => $f['linea'], 'clave' => self::clave($v),
				'nombre_materia' => $v['nombre_materia'] ?? '', 'cod_carrera' => $v['cod_carrera'] ?? '',
				'avisos' => $avisos);
			$motivos = $f['problemas'];
			$k = $base['clave'];
			if (!$motivos && $veces[$k] > 1) {
				$motivos[] = 'La misma materia (cod_guarani, plan_guarani y version_guarani) esta ' . $veces[$k]
					. ' veces en el archivo.';
			}
			$hay = $existentes[$k] ?? array();
			if (!$motivos && count($hay) > 1) {
				$ids = array();
				foreach ($hay as $m) {
					$ids[] = $m['id_materia'];
				}
				$motivos[] = 'En Catedras ya hay ' . count($hay) . ' materias con esa clave (id ' . implode(', ', $ids)
					. '): resolverlo a mano en Catedras.';
			}
			if (!$motivos && !$hay) {
				$faltan = array();
				foreach (self::$OBLIGATORIOS_ALTA as $c) {
					if (!isset($v[$c])) {
						$faltan[] = $c;
					}
				}
				if ($faltan) {
					$motivos[] = 'Es una materia nueva y le falta ' . implode(', ', $faltan) . '.';
				}
			}
			if ($motivos) {
				$r['problemas'][] = $base + array('motivos' => $motivos);
				continue;
			}
			if (!$hay) {
				$r['altas'][] = $base + array('valores' => $v);
				continue;
			}
			$m = $hay[0];
			$cambios = array();
			$no_se_tocan = array();
			$elegidos = ($por_linea !== null && isset($por_linea[$f['linea']])) ? $por_linea[$f['linea']] : $campos;
			foreach ($v as $c => $nuevo) {
				if (!self::igual($c, $m[$c], $nuevo)) {
					$cambio = array('campo' => $c, 'antes' => $m[$c] === null ? '' : (string) $m[$c],
						'despues' => (string) $nuevo);
					$con_cambios[$c] = true;
					if ($elegidos === null || in_array($c, $elegidos, true)) {
						$cambios[] = $cambio;
					} else {
						$no_se_tocan[] = $cambio;
					}
				}
			}
			$fila = $base + array('id_materia' => (int) $m['id_materia'], 'no_se_tocan' => $no_se_tocan);
			if ($fila['nombre_materia'] === '') {
				$fila['nombre_materia'] = (string) $m['nombre_materia'];
			}
			if ($fila['cod_carrera'] === '') {
				$fila['cod_carrera'] = (string) $m['cod_carrera'];
			}
			if ($cambios) {
				$r['modificaciones'][] = $fila + array('cambios' => $cambios);
			} else {
				$r['sin_cambios'][] = $fila;
			}
		}
		// En el orden de la tabla.
		foreach (array_keys(self::$COLUMNAS) as $c) {
			if (isset($con_cambios[$c])) {
				$r['campos_con_cambios'][] = $c;
			}
		}
		return $r;
	}

	/**
	 * Planifica y, si no es simular, aplica en una transaccion. Devuelve el
	 * plan; las altas con el id_materia nuevo. Levanta la excepcion de la base
	 * si algo falla (y no queda nada). $campos, $por_linea: ver planificar.
	 */
	static function aplicar($filas, $usuario, $simular, $campos = null, $por_linea = null)
	{
		$db = self::db();
		if ($simular) {
			return self::planificar($db, $filas, $campos, $por_linea);
		}
		$db->abrir_transaccion();
		try {
			// Que nadie de de alta la misma materia entre planificar y guardar.
			$db->ejecutar('LOCK TABLE ' . self::TABLA . ' IN SHARE ROW EXCLUSIVE MODE');
			$r = self::planificar($db, $filas, $campos, $por_linea);
			foreach ($r['altas'] as $i => $a) {
				$cols = array_keys($a['valores']);
				$vals = array();
				foreach ($a['valores'] as $c => $v) {
					$vals[] = self::$COLUMNAS[$c] === 'int' ? (int) $v : quote($v);
				}
				$sql = 'INSERT INTO ' . self::TABLA . ' (' . implode(', ', $cols) . ') VALUES ('
					. implode(', ', $vals) . ') RETURNING id_materia';
				$nueva = $db->consultar_fila($sql);
				$r['altas'][$i]['id_materia'] = (int) $nueva['id_materia'];
			}
			foreach ($r['modificaciones'] as $m) {
				$sets = array();
				foreach ($m['cambios'] as $c) {
					$v = $c['despues'];
					$sets[] = $c['campo'] . ' = ' . (self::$COLUMNAS[$c['campo']] === 'int' ? (int) $v : quote($v));
				}
				$db->ejecutar('UPDATE ' . self::TABLA . ' SET ' . implode(', ', $sets)
					. ' WHERE id_materia = ' . (int) $m['id_materia']);
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('MATERIAS (alta/modificacion): ' . $e->getMessage());
			throw $e;
		}
		$ids = array();
		foreach ($r['altas'] as $a) {
			$ids[] = $a['id_materia'];
		}
		$mods = array();
		foreach ($r['modificaciones'] as $m) {
			$mods[] = $m['id_materia'];
		}
		toba::logger()->notice('MATERIAS por ' . $usuario . ': altas [' . implode(', ', $ids) . '], modificadas ['
			. implode(', ', $mods) . '], ' . count($r['problemas']) . ' con problemas');
		return $r;
	}
}
