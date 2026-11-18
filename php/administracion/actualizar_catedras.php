<?php
if (!defined('CATEDRAS_IMPORTAR_PROGRAMAS_SIN_EJECUTAR')) {
	define('CATEDRAS_IMPORTAR_PROGRAMAS_SIN_EJECUTAR', true);
}
require_once dirname(__FILE__) . '/importar_programas.php';

/**
 * Actualizar desde Mocovi lo que ya existe en Catedras (desde 2027). Lo usa
 * deptos (/programas), que arma con el informe de Mocovi del anio quien es
 * el responsable de cada dictado y su equipo, y dice si el plazo de cada
 * documento sigue vigente segun el calendario academico (que esta en deptos).
 *
 * Programas (POST /rest/actualizar-programas): el responsable (legajo, DNI,
 * apellido, nombre, cargo, designacion) y el equipo de catedra del programa,
 * que la planificacion toma del programa. Solo si el programa y su
 * planificacion estan en estado docente y el plazo del programa no vencio.
 * Si el responsable cambia y el informe ya se envio con el responsable
 * anterior, el informe se queda con el anterior (se le fija).
 *
 * Informes (POST /rest/actualizar-informes): crea el informe de catedra de
 * los programas que no lo tienen (vacio, en estado docente) y pone el
 * responsable del informe (informes.*_resp_inf, ver
 * deploy/sql/informes_responsable.sql), que puede no ser el del programa: el
 * programa se envio en marzo y el informe se completa en diciembre. Solo si
 * el informe esta en estado docente y su plazo no vencio. Si coincide con el
 * del programa, las columnas quedan en NULL (el del programa).
 *
 * planificar_* no guarda nada; aplicar_* vuelve a planificar con las tablas
 * bloqueadas y guarda todo en una transaccion. Mandar lo mismo dos veces no
 * cambia nada la segunda.
 *
 * Los textos van en LATIN1, como la base (la API REST ya convierte el JSON).
 * Archivo en ASCII.
 */
class catedras_actualizar
{
	/** Desde que anio academico se actualiza (2025 y 2026 quedan como estan). */
	const DESDE = 2027;

	/** Las columnas del responsable en programas. */
	static $RESP = array('legajo_resp', 'dni_resp', 'id_designacion', 'cargo_resp', 'apellido_resp', 'nombre_resp');

	static function db()
	{
		return toba::db('catedras');
	}

	/** {legajo => dni} de los usuarios de Toba ('' si no tiene DNI). */
	static function dnis()
	{
		$dni_de = array();
		foreach (toba::instancia()->get_db()->consultar('SELECT usuario, ciu FROM apex_usuario') as $u) {
			$dni_de[trim((string) $u['usuario'])] = preg_replace('/\D/', '', (string) $u['ciu']);
		}
		return $dni_de;
	}

	static function texto($fila, $campo)
	{
		return (isset($fila[$campo]) && !is_array($fila[$campo])) ? trim(preg_replace('/\s+/', ' ', (string) $fila[$campo])) : '';
	}

	/** Las filas que manda deptos, limpias (las de los dos recursos). */
	static function normalizar($filas)
	{
		$salida = array();
		foreach (array_values($filas) as $i => $c) {
			if (!is_array($c)) {
				throw new InvalidArgumentException('La fila ' . ($i + 1) . ' no es un objeto.');
			}
			$f = array('linea' => $i + 1);
			foreach (array('id_programa', 'legajo_resp', 'id_designacion', 'cargo_resp', 'apellido_resp',
				'nombre_resp', 'equipo_catedra', 'materia') as $k) {
				$f[$k] = self::texto($c, $k);
			}
			$f['vigente'] = !empty($c['vigente']);
			if (!ctype_digit($f['id_programa'])) {
				throw new InvalidArgumentException('La fila ' . ($i + 1) . ' no tiene id_programa.');
			}
			$salida[] = $f;
		}
		return $salida;
	}

	/** Un entero de la base o de una fila, o null. */
	static function entero($v)
	{
		$v = trim((string) $v);
		return ($v !== '' && ctype_digit($v)) ? (int) $v : null;
	}

	/** El equipo como lista para comparar: el JSON decodificado, [] si esta vacio. */
	static function equipo_lista($v)
	{
		$v = trim((string) $v);
		if ($v === '') {
			return array();
		}
		$d = json_decode($v, true);
		return is_array($d) ? $d : array('texto' => $v);
	}

	/**
	 * Lo que cambia del responsable: {columna => array(antes, despues)}. $actual
	 * y $nuevo con las columnas de programas (legajo_resp, ...). El DNI no se
	 * compara: sale del legajo.
	 */
	static function cambios_resp($actual, $nuevo)
	{
		$c = array();
		foreach (array('legajo_resp', 'id_designacion') as $k) {
			if (self::entero($actual[$k]) !== self::entero($nuevo[$k])) {
				$c[$k] = array($actual[$k], $nuevo[$k]);
			}
		}
		foreach (array('cargo_resp', 'apellido_resp', 'nombre_resp') as $k) {
			if (trim((string) $actual[$k]) !== trim((string) $nuevo[$k])) {
				$c[$k] = array($actual[$k], $nuevo[$k]);
			}
		}
		return $c;
	}

	/** id_programa => fila de programas, de las que vienen. */
	static function programas($filas)
	{
		$ids = array();
		foreach ($filas as $f) {
			$ids[] = (int) $f['id_programa'];
		}
		$salida = array();
		if (!$ids) {
			return $salida;
		}
		$sql = 'SELECT p.id_programa, trim(p.ano_academico) AS ano, p.estado, p.legajo_resp, p.dni_resp, p.id_designacion,
				p.cargo_resp, p.apellido_resp, p.nombre_resp, p.equipo_catedra,
				(SELECT string_agg(DISTINCT pl.estado_planificacion, \',\') FROM planificaciones pl
					WHERE pl.id_prog_planif = p.id_programa) AS estados_planif
			FROM programas p WHERE p.id_programa IN (' . implode(', ', array_unique($ids)) . ')';
		foreach (self::db()->consultar($sql) as $p) {
			$salida[(int) $p['id_programa']] = $p;
		}
		return $salida;
	}

	/** id_programa => lista de informes (con su responsable propio). */
	static function informes_de($ids)
	{
		$salida = array();
		if (!$ids) {
			return $salida;
		}
		$sql = 'SELECT id_informe, id_prog_informe, estado_informe, id_designacion_inf, legajo_resp_inf, dni_resp_inf,
				apellido_resp_inf, nombre_resp_inf, cargo_resp_inf
			FROM informes WHERE id_prog_informe IN (' . implode(', ', array_map('intval', array_unique($ids))) . ')
			ORDER BY id_informe';
		foreach (self::db()->consultar($sql) as $i) {
			$salida[(int) $i['id_prog_informe']][] = $i;
		}
		return $salida;
	}

	/** El responsable que vale para un informe: el suyo o el de su programa. */
	static function resp_informe($inf, $prog)
	{
		// Con legajo propio, todo el responsable es el del informe.
		$propio = $inf['legajo_resp_inf'] !== null && $inf['legajo_resp_inf'] !== '';
		$r = array();
		foreach (self::$RESP as $k) {
			$r[$k] = $propio ? ($inf[$k . '_inf'] ?? null) : $prog[$k];
		}
		return $r;
	}

	//-------------------------------------------------------------------------
	// Programas
	//-------------------------------------------------------------------------

	/**
	 * array('actualizar' => [...], 'sin_cambios' => [...], 'no_se_tocan' => [...],
	 * 'problemas' => [...]); cada uno con 'fila', 'id' y, segun el caso,
	 * 'cambios' ({columna => array(antes, despues)}), 'motivo' o 'motivos'.
	 */
	static function planificar_programas($filas)
	{
		$plan = array('actualizar' => array(), 'sin_cambios' => array(), 'no_se_tocan' => array(), 'problemas' => array());
		$progs = self::programas($filas);
		$dni_de = null;
		$vistos = array();
		foreach ($filas as $f) {
			$id = (int) $f['id_programa'];
			if (isset($vistos[$id])) {
				$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'motivos' => array('repetido (fila ' . $vistos[$id] . ')'));
				continue;
			}
			$vistos[$id] = $f['linea'];
			if (!isset($progs[$id])) {
				$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'motivos' => array('el programa no existe'));
				continue;
			}
			$p = $progs[$id];
			if ((int) $p['ano'] < self::DESDE) {
				$plan['no_se_tocan'][] = array('fila' => $f, 'id' => $id, 'motivo' => 'es de ' . $p['ano'] . ': solo desde ' . self::DESDE);
				continue;
			}
			$cambios = self::cambios_resp($p, $f);
			if (self::equipo_lista($p['equipo_catedra']) !== self::equipo_lista($f['equipo_catedra'])) {
				$cambios['equipo_catedra'] = array($p['equipo_catedra'], $f['equipo_catedra']);
			}
			if (!$cambios) {
				$plan['sin_cambios'][] = array('fila' => $f, 'id' => $id);
				continue;
			}
			$motivo = null;
			$planifs = $p['estados_planif'] === null ? array() : explode(',', $p['estados_planif']);
			if ($p['estado'] !== 'docente') {
				$motivo = 'el programa ya se envi&oacute; (estado ' . $p['estado'] . ')';
			} elseif (array_diff($planifs, array('docente'))) {
				$motivo = 'la planificaci&oacute;n ya se envi&oacute;';
			} elseif (!$f['vigente']) {
				$motivo = 'venci&oacute; el plazo del programa';
			}
			if ($motivo !== null) {
				$plan['no_se_tocan'][] = array('fila' => $f, 'id' => $id, 'motivo' => $motivo, 'cambios' => $cambios);
				continue;
			}
			if (isset($cambios['legajo_resp'])) {
				if ($dni_de === null) {
					$dni_de = self::dnis();
				}
				$dni = $dni_de[$f['legajo_resp']] ?? null;
				if ($dni === null || $dni === '') {
					$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'cambios' => $cambios, 'motivos' => array(
						$dni === null ? 'el legajo ' . $f['legajo_resp'] . ' no tiene usuario en C&aacute;tedras'
							: 'el usuario ' . $f['legajo_resp'] . ' no tiene DNI cargado'));
					continue;
				}
				$f['dni_resp'] = $dni;
			}
			$plan['actualizar'][] = array('fila' => $f, 'id' => $id, 'cambios' => $cambios, 'antes' => $p);
		}
		return $plan;
	}

	static function aplicar_programas($filas, $usuario)
	{
		$db = self::db();
		$db->abrir_transaccion();
		try {
			$db->ejecutar('LOCK TABLE programas, planificaciones, informes IN SHARE ROW EXCLUSIVE MODE');
			$plan = self::planificar_programas($filas);
			$q = function ($v) use ($db) {
				return ($v === null || $v === '') ? 'NULL' : $db->quote($v);
			};
			$informes = self::informes_de(array_map(function ($a) {
				return $a['id'];
			}, $plan['actualizar']));
			foreach ($plan['actualizar'] as $a) {
				$f = $a['fila'];
				$p = $a['antes'];
				$set = array();
				if (isset($a['cambios']['legajo_resp'])) {
					$set[] = 'legajo_resp = ' . (int) $f['legajo_resp'];
					$set[] = 'dni_resp = ' . (int) $f['dni_resp'];
				}
				foreach (array('id_designacion') as $k) {
					if (isset($a['cambios'][$k])) {
						$set[] = "$k = " . (self::entero($f[$k]) === null ? 'NULL' : (string) self::entero($f[$k]));
					}
				}
				foreach (array('cargo_resp', 'apellido_resp', 'nombre_resp', 'equipo_catedra') as $k) {
					if (isset($a['cambios'][$k])) {
						$set[] = "$k = " . $q($f[$k]);
					}
				}
				$db->ejecutar('UPDATE programas SET ' . implode(', ', $set) . ' WHERE id_programa = ' . (int) $a['id']
					. " AND estado = 'docente'");
				// Un informe ya enviado con el responsable anterior se queda con ese.
				if (isset($a['cambios']['legajo_resp'])) {
					foreach ($informes[$a['id']] ?? array() as $inf) {
						if ($inf['legajo_resp_inf'] === null && $inf['estado_informe'] !== 'docente') {
							$db->ejecutar('UPDATE informes SET legajo_resp_inf = ' . (int) $p['legajo_resp']
								. ', dni_resp_inf = ' . (self::entero($p['dni_resp']) === null ? 'NULL' : (int) $p['dni_resp'])
								. ', id_designacion_inf = ' . (self::entero($p['id_designacion']) === null ? 'NULL' : (int) $p['id_designacion'])
								. ', cargo_resp_inf = ' . $q($p['cargo_resp']) . ', apellido_resp_inf = ' . $q($p['apellido_resp'])
								. ', nombre_resp_inf = ' . $q($p['nombre_resp']) . ', resp_inf_actualizado = now(), resp_inf_por = '
								. $q(substr($usuario, 0, 100)) . ' WHERE id_informe = ' . (int) $inf['id_informe']);
						}
					}
				}
				$obs = 'Actualizado desde Mocovi: ' . implode(', ', array_map(function ($k) use ($a) {
					return $k === 'equipo_catedra' ? 'equipo de c&aacute;tedra'
						: $k . ' ' . $a['cambios'][$k][0] . ' &rarr; ' . $a['cambios'][$k][1];
				}, array_keys($a['cambios'])));
				$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) VALUES ('programas', "
					. (int) $a['id'] . ", 'Actualizacion', " . $db->quote($usuario) . ', ' . $db->quote($obs) . ", 'docente')");
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('ACTUALIZAR PROGRAMAS: ' . $e->getMessage());
			throw $e;
		}
		toba::logger()->notice('ACTUALIZAR PROGRAMAS por ' . $usuario . ': ' . count($plan['actualizar']) . ' actualizados');
		return $plan;
	}

	//-------------------------------------------------------------------------
	// Informes
	//-------------------------------------------------------------------------

	/**
	 * array('crear' => [...], 'actualizar' => [...], 'sin_cambios' => [...],
	 * 'no_se_tocan' => [...], 'problemas' => [...]).
	 */
	static function planificar_informes($filas)
	{
		$plan = array('crear' => array(), 'actualizar' => array(), 'sin_cambios' => array(), 'no_se_tocan' => array(),
			'problemas' => array());
		$progs = self::programas($filas);
		$informes = self::informes_de(array_keys($progs));
		$dni_de = self::dnis();
		$vistos = array();
		foreach ($filas as $f) {
			$id = (int) $f['id_programa'];
			if (isset($vistos[$id])) {
				$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'motivos' => array('repetido (fila ' . $vistos[$id] . ')'));
				continue;
			}
			$vistos[$id] = $f['linea'];
			if (!isset($progs[$id])) {
				$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'motivos' => array('el programa no existe'));
				continue;
			}
			$p = $progs[$id];
			if ((int) $p['ano'] < self::DESDE) {
				$plan['no_se_tocan'][] = array('fila' => $f, 'id' => $id, 'motivo' => 'es de ' . $p['ano'] . ': solo desde ' . self::DESDE);
				continue;
			}
			// Sin legajo: solo crear el informe si falta, con el responsable del
			// programa (deptos no sabe cual va: sin Resp en Mocovi o doble sin elegir).
			$solo_crear = $f['legajo_resp'] === '';
			if ($solo_crear) {
				foreach (self::$RESP as $k) {
					$f[$k] = (string) $p[$k];
				}
			}
			$dni = $dni_de[$f['legajo_resp']] ?? null;
			$sin_dni = ($dni === null || $dni === '')
				? ($dni === null ? 'el legajo ' . $f['legajo_resp'] . ' no tiene usuario en C&aacute;tedras'
					: 'el usuario ' . $f['legajo_resp'] . ' no tiene DNI cargado')
				: null;
			$f['dni_resp'] = $dni;
			$del_programa = !self::cambios_resp($p, $f);
			$lista = $informes[$id] ?? array();
			if (!$lista) {
				// Sin informe: se crea; con el responsable del programa o con el suyo.
				if (!$del_programa && $sin_dni !== null) {
					$plan['problemas'][] = array('fila' => $f, 'id' => $id, 'motivos' => array($sin_dni));
					continue;
				}
				$plan['crear'][] = array('fila' => $f, 'id' => $id, 'del_programa' => $del_programa,
					'cambios' => $del_programa ? array() : self::cambios_resp($p, $f));
				continue;
			}
			foreach ($lista as $inf) {
				$actual = self::resp_informe($inf, $p);
				$cambios = self::cambios_resp($actual, $f);
				$r = array('fila' => $f, 'id' => $id, 'id_informe' => (int) $inf['id_informe']);
				if (!$cambios || $solo_crear) {
					$plan['sin_cambios'][] = $r;
				} elseif ($inf['estado_informe'] !== 'docente') {
					$plan['no_se_tocan'][] = $r + array('cambios' => $cambios,
						'motivo' => 'el informe ya se envi&oacute; (estado ' . $inf['estado_informe'] . ')');
				} elseif (!$f['vigente']) {
					$plan['no_se_tocan'][] = $r + array('cambios' => $cambios, 'motivo' => 'venci&oacute; el plazo del informe');
				} elseif (!$del_programa && $sin_dni !== null) {
					$plan['problemas'][] = $r + array('cambios' => $cambios, 'motivos' => array($sin_dni));
				} else {
					$plan['actualizar'][] = $r + array('cambios' => $cambios, 'del_programa' => $del_programa);
				}
			}
		}
		return $plan;
	}

	/** El SET del responsable propio de un informe: NULL si es el del programa. */
	static function set_resp_informe($db, $f, $del_programa, $usuario)
	{
		$q = function ($v) use ($db) {
			return ($v === null || $v === '') ? 'NULL' : $db->quote($v);
		};
		$n = function ($v) {
			$v = self::entero($v);
			return $v === null ? 'NULL' : (string) $v;
		};
		if ($del_programa) {
			$vals = array('legajo_resp_inf' => 'NULL', 'dni_resp_inf' => 'NULL', 'id_designacion_inf' => 'NULL',
				'cargo_resp_inf' => 'NULL', 'apellido_resp_inf' => 'NULL', 'nombre_resp_inf' => 'NULL');
		} else {
			$vals = array('legajo_resp_inf' => $n($f['legajo_resp']), 'dni_resp_inf' => $n($f['dni_resp']),
				'id_designacion_inf' => $n($f['id_designacion']), 'cargo_resp_inf' => $q($f['cargo_resp']),
				'apellido_resp_inf' => $q($f['apellido_resp']), 'nombre_resp_inf' => $q($f['nombre_resp']));
		}
		$vals['resp_inf_actualizado'] = 'now()';
		$vals['resp_inf_por'] = $q(substr($usuario, 0, 100));
		return $vals;
	}

	static function aplicar_informes($filas, $usuario)
	{
		$db = self::db();
		$creados = array();
		$db->abrir_transaccion();
		try {
			$db->ejecutar('LOCK TABLE programas, informes IN SHARE ROW EXCLUSIVE MODE');
			$plan = self::planificar_informes($filas);
			$max = $db->consultar_fila('SELECT coalesce(max(id_informe), 0) AS n FROM informes');
			$siguiente = (int) $max['n'];
			foreach ($plan['crear'] as $c) {
				$siguiente++;
				catedras_importar_programas::crear_informe($db, $siguiente, (int) $c['id'], $usuario,
					'Creado desde deptos para el programa ' . (int) $c['id']);
				if (!$c['del_programa']) {
					$vals = self::set_resp_informe($db, $c['fila'], false, $usuario);
					$db->ejecutar('UPDATE informes SET ' . implode(', ', array_map(function ($k, $v) {
						return "$k = $v";
					}, array_keys($vals), $vals)) . ' WHERE id_informe = ' . $siguiente);
				}
				$creados[] = array('linea' => $c['fila']['linea'], 'id_programa' => (int) $c['id'], 'id_informe' => $siguiente);
			}
			foreach ($plan['actualizar'] as $a) {
				$vals = self::set_resp_informe($db, $a['fila'], $a['del_programa'], $usuario);
				$db->ejecutar('UPDATE informes SET ' . implode(', ', array_map(function ($k, $v) {
					return "$k = $v";
				}, array_keys($vals), $vals)) . ' WHERE id_informe = ' . (int) $a['id_informe'] . " AND estado_informe = 'docente'");
				$obs = 'Responsable desde Mocovi: ' . implode(', ', array_map(function ($k) use ($a) {
					return $k . ' ' . $a['cambios'][$k][0] . ' &rarr; ' . $a['cambios'][$k][1];
				}, array_keys($a['cambios'])));
				$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) VALUES ('informes', "
					. (int) $a['id_informe'] . ", 'Actualizacion', " . $db->quote($usuario) . ', ' . $db->quote($obs) . ", 'docente')");
			}
			catedras_importar_programas::adelantar_secuencia($db, 'informes_id_informe_seq', 'informes', 'id_informe');
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('ACTUALIZAR INFORMES: ' . $e->getMessage());
			throw $e;
		}
		toba::logger()->notice('ACTUALIZAR INFORMES por ' . $usuario . ': ' . count($creados) . ' creados, '
			. count($plan['actualizar']) . ' con otro responsable');
		$plan['creados'] = $creados;
		return $plan;
	}
}
