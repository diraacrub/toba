<?php
/**
 * El equipo de cada materia por anio (huayca.materias_equipo, ver
 * deploy/sql/materias_equipo.sql), tal como lo trae el informe de Mocovi.
 *
 * Lo escribe POST /rest/equipo-mocovi cada vez que Secretaria sube el informe
 * en deptos (siempre completo, siempre simulado antes). Por anio:
 *   - fila identica (las columnas de Mocovi y el anio) y activa: no se toca;
 *   - fila de la tabla que no viene en el informe: pasa a 'borrado';
 *   - fila borrada que vuelve identica: se reactiva (conserva observaciones);
 *   - fila nueva: se agrega.
 *
 * Lo lee el informe de catedra (punto 11): opciones() da los docentes de un
 * dictado para el select del ML, activos y dados de baja en el anio.
 *
 * Los textos van en LATIN1, como la base (la API REST ya convierte el JSON).
 * Archivo en ASCII.
 */
class materias_equipo
{
	const TABLA = 'huayca.materias_equipo';

	/** Columna de la tabla => clave de la fila que manda deptos (la de programa_mocovi). */
	static $COLUMNAS = array(
		'materia' => 'materia',
		'cod_guarani' => 'cod_siu',
		'cod_carrera' => 'carrera',
		'plan_mocovi' => 'ordenanza',
		'docente' => 'docente',
		'legajo' => 'legajo',
		'categoria' => 'categoria',
		'id_designacion' => 'desig',
		'modulo' => 'modulo',
		'rol' => 'rol',
		'periodo' => 'periodo',
	);

	static function db()
	{
		return toba::db('catedras');
	}

	/** Espacios de mas afuera y adentro, como los deja deptos. */
	static function limpio($v)
	{
		return trim(preg_replace('/\s+/', ' ', (string) $v));
	}

	/** La clave de "fila identica": las columnas de Mocovi, en orden. */
	static function clave($fila)
	{
		$partes = array();
		foreach (array_keys(self::$COLUMNAS) as $c) {
			$partes[] = self::limpio(isset($fila[$c]) ? $fila[$c] : '');
		}
		return implode("\x1f", $partes);
	}

	/**
	 * Las filas que manda deptos (claves de $COLUMNAS) -> filas con las
	 * columnas de la tabla. Levanta Exception con el numero de fila si falta algo.
	 */
	static function normalizar($filas)
	{
		$salida = array();
		foreach (array_values($filas) as $i => $f) {
			$r = array();
			foreach (self::$COLUMNAS as $columna => $campo) {
				$v = (is_array($f) && isset($f[$campo]) && !is_array($f[$campo])) ? self::limpio($f[$campo]) : '';
				$r[$columna] = $v;
			}
			foreach (array('cod_guarani', 'cod_carrera', 'plan_mocovi', 'docente', 'legajo') as $obligatoria) {
				if ($r[$obligatoria] === '') {
					throw new InvalidArgumentException('La fila ' . ($i + 2) . " del informe no tiene $obligatoria.");
				}
			}
			$salida[] = $r;
		}
		return $salida;
	}

	/** {cod_guarani|cod_carrera|plan_mocovi => id_materia} del catalogo (el primero, si hay dos). */
	static function indice_materias()
	{
		$indice = array();
		$filas = self::db()->consultar('SELECT id_materia, trim(cod_guarani) AS c, trim(cod_carrera) AS k,
			trim(plan_mocovi) AS p FROM huayca.materias ORDER BY id_materia');
		foreach ($filas as $m) {
			$k = $m['c'] . '|' . $m['k'] . '|' . $m['p'];
			if (!isset($indice[$k])) {
				$indice[$k] = (int) $m['id_materia'];
			}
		}
		return $indice;
	}

	/** Lo que hay en la tabla para ese anio: {clave => fila}. */
	static function existentes($ano)
	{
		$salida = array();
		$filas = self::db()->consultar('SELECT * FROM ' . self::TABLA . ' WHERE ano_academico = ' . (int) $ano
			. ' ORDER BY id_materia_equipo');
		foreach ($filas as $f) {
			$k = self::clave($f);
			// Si hubiera dos identicas (no deberia), cuenta la activa.
			if (!isset($salida[$k]) || $f['estado'] === 'activo') {
				$salida[$k] = $f;
			}
		}
		return $salida;
	}

	/**
	 * Que pasaria al aplicar el informe de ese anio. Devuelve
	 * array('agregar' => filas, 'reactivar' => filas de la tabla,
	 *       'borrar' => filas de la tabla, 'iguales' => n, 'repetidas' => n,
	 *       'sin_materia' => filas nuevas que no estan en el catalogo).
	 */
	static function planificar($ano, $filas)
	{
		$ano = (int) $ano;
		$existentes = self::existentes($ano);
		$indice = self::indice_materias();
		$plan = array('agregar' => array(), 'reactivar' => array(), 'borrar' => array(),
			'iguales' => 0, 'repetidas' => 0, 'sin_materia' => array());
		$vistas = array();
		foreach ($filas as $f) {
			$k = self::clave($f);
			if (isset($vistas[$k])) {
				$plan['repetidas']++;
				continue;
			}
			$vistas[$k] = true;
			if (isset($existentes[$k])) {
				if ($existentes[$k]['estado'] === 'activo') {
					$plan['iguales']++;
				} else {
					$plan['reactivar'][] = $existentes[$k];
				}
				continue;
			}
			$km = $f['cod_guarani'] . '|' . $f['cod_carrera'] . '|' . $f['plan_mocovi'];
			$f['id_materia'] = isset($indice[$km]) ? $indice[$km] : null;
			if ($f['id_materia'] === null) {
				$plan['sin_materia'][] = $f;
			}
			$plan['agregar'][] = $f;
		}
		foreach ($existentes as $k => $e) {
			if (!isset($vistas[$k]) && $e['estado'] === 'activo') {
				$plan['borrar'][] = $e;
			}
		}
		return $plan;
	}

	/**
	 * Aplica el informe: vuelve a planificar con la tabla bloqueada (lo que se
	 * simulo puede haber cambiado) y escribe todo en una transaccion.
	 * Devuelve el plan aplicado.
	 */
	static function aplicar($ano, $filas, $usuario)
	{
		$db = self::db();
		$ano = (int) $ano;
		$usuario = substr((string) $usuario, 0, 100);
		$db->abrir_transaccion();
		try {
			$db->ejecutar('LOCK TABLE ' . self::TABLA . ' IN SHARE ROW EXCLUSIVE MODE');
			$plan = self::planificar($ano, $filas);
			$q = function ($v) use ($db) {
				return $v === null ? 'NULL' : $db->quote($v);
			};
			$columnas = array_keys(self::$COLUMNAS);
			foreach ($plan['agregar'] as $f) {
				$valores = array((string) $ano);
				foreach ($columnas as $c) {
					$valores[] = $q($f[$c]);
				}
				$valores[] = $f['id_materia'] === null ? 'NULL' : (string) (int) $f['id_materia'];
				$valores[] = $q($usuario);
				$db->ejecutar('INSERT INTO ' . self::TABLA . ' (ano_academico, ' . implode(', ', $columnas)
					. ', id_materia, actualizado_por) VALUES (' . implode(', ', $valores) . ')');
			}
			$ids = function ($lista) {
				return implode(', ', array_map(function ($f) {
					return (string) (int) $f['id_materia_equipo'];
				}, $lista));
			};
			if (!empty($plan['reactivar'])) {
				$db->ejecutar('UPDATE ' . self::TABLA . " SET estado = 'activo', baja = NULL, actualizado = now(),
					actualizado_por = " . $q($usuario) . ' WHERE id_materia_equipo IN (' . $ids($plan['reactivar']) . ')');
			}
			if (!empty($plan['borrar'])) {
				$db->ejecutar('UPDATE ' . self::TABLA . " SET estado = 'borrado', baja = now(), actualizado = now(),
					actualizado_por = " . $q($usuario) . ' WHERE id_materia_equipo IN (' . $ids($plan['borrar']) . ')');
			}
			$obs = 'Informe de Mocovi ' . $ano . ': ' . count($plan['agregar']) . ' agregadas, '
				. count($plan['reactivar']) . ' reactivadas, ' . count($plan['borrar']) . ' borradas, '
				. $plan['iguales'] . ' sin cambios';
			$db->ejecutar('INSERT INTO huayca.movimientos (nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento,
				usuario_movimiento, observaciones, estado_mov) VALUES (' . $q('materias_equipo') . ', ' . $ano
				. ', now(), ' . $q('Informe de Mocovi') . ', ' . $q($usuario) . ', ' . $q($obs) . ', NULL)');
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			throw $e;
		}
		return $plan;
	}

	//-------------------------------------------------------------------------
	// El select del informe (punto 11)
	//-------------------------------------------------------------------------

	/** 'JTP3' -> array('JTP', '3'). ASD es JTP, como en el equipo de los programas. */
	static function cargo_dedicacion($categoria)
	{
		$c = strtoupper(str_replace(array(' ', '-'), '', (string) $categoria));
		$cargo = substr($c, 0, 3);
		$ded = substr($c, 3, 1);
		if ($cargo === 'ASD') {
			$cargo = 'JTP';
		}
		return array($cargo, $ded === false ? '' : $ded);
	}

	/** Las palabras de un nombre, sin tildes ni mayusculas ni comas, ordenadas. */
	static function palabras($nombre)
	{
		$t = strtr(strtoupper((string) $nombre), "\xe1\xe9\xed\xf3\xfa\xfc\xf1\xc1\xc9\xcd\xd3\xda\xdc\xd1",
			'AEIOUUNAEIOUUN');
		$p = preg_split('/[^A-Z]+/', $t, -1, PREG_SPLIT_NO_EMPTY);
		sort($p);
		return implode(' ', $p);
	}

	/**
	 * La opcion de $opciones (las de opciones()) cuyo docente tiene las mismas
	 * palabras que $nombre, si es una sola persona (un legajo; si tiene dos
	 * categorias, no se elige). null si no hay o hay dudas.
	 */
	static function por_nombre($nombre, $opciones)
	{
		$buscado = self::palabras($nombre);
		if ($buscado === '') {
			return null;
		}
		$encontradas = array();
		foreach ($opciones as $k => $o) {
			if (!empty($o['legajo']) && self::palabras($o['docente']) === $buscado) {
				$encontradas[$k] = $o['legajo'];
			}
		}
		return count($encontradas) === 1 ? key($encontradas) : null;
	}

	/** La clave de una opcion del select: legajo y categoria ('57390|JTP3'). */
	static function clave_opcion($legajo, $categoria)
	{
		return self::limpio($legajo) . '|' . strtoupper(self::limpio($categoria));
	}

	/**
	 * Los docentes de un dictado para el select: los de la materia (cod_guarani,
	 * cod_carrera, plan_mocovi) en ese anio y periodo; si en ese periodo no hay
	 * nadie, los de cualquier periodo del anio. Una opcion por legajo y
	 * categoria, activos primero. Vacio si la materia no esta en la tabla
	 * (entonces el informe usa texto libre).
	 *
	 * Devuelve array(clave => array('clave', 'descripcion', 'docente', 'legajo',
	 * 'categoria', 'cargo', 'dedicacion', 'activo')).
	 */
	static function opciones($cod_guarani, $cod_carrera, $plan_mocovi, $ano, $periodo = null)
	{
		$db = self::db();
		$sql = 'SELECT docente, legajo, categoria, periodo, estado FROM ' . self::TABLA
			. ' WHERE ano_academico = ' . (int) $ano
			. ' AND cod_guarani = ' . $db->quote(self::limpio($cod_guarani))
			. ' AND cod_carrera = ' . $db->quote(self::limpio($cod_carrera))
			. ' AND plan_mocovi = ' . $db->quote(self::limpio($plan_mocovi))
			. ' ORDER BY docente, categoria';
		$filas = $db->consultar($sql);
		$periodo = strtoupper(self::limpio($periodo));
		$del_periodo = array_filter($filas, function ($f) use ($periodo) {
			return strtoupper(self::limpio($f['periodo'])) === $periodo;
		});
		if (!empty($del_periodo)) {
			$filas = $del_periodo;
		}
		$opciones = array();
		foreach ($filas as $f) {
			$k = self::clave_opcion($f['legajo'], $f['categoria']);
			$activo = $f['estado'] === 'activo';
			if (isset($opciones[$k])) {
				$opciones[$k]['activo'] = $opciones[$k]['activo'] || $activo;
				continue;
			}
			list($cargo, $ded) = self::cargo_dedicacion($f['categoria']);
			$opciones[$k] = array('clave' => $k, 'docente' => self::limpio($f['docente']),
				'legajo' => self::limpio($f['legajo']), 'categoria' => strtoupper(self::limpio($f['categoria'])),
				'cargo' => $cargo, 'dedicacion' => $ded, 'activo' => $activo);
		}
		foreach ($opciones as $k => $o) {
			$opciones[$k]['descripcion'] = $o['docente'] . ' (' . $o['categoria'] . ')' . ($o['activo'] ? '' : ' - baja');
		}
		uasort($opciones, function ($a, $b) {
			if ($a['activo'] !== $b['activo']) {
				return $a['activo'] ? -1 : 1;
			}
			return strcmp($a['descripcion'], $b['descripcion']);
		});
		return $opciones;
	}
}
