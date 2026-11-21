<?php
require_once dirname(__FILE__) . '/materias_equipo.php';

/**
 * Los inscriptos de cada materia por anio y envio (huayca.materias_inscripto,
 * ver deploy/sql/materias_inscripto.sql): que DNI cursa que materia en que
 * carrera. Los manda deptos (POST /rest/inscriptos) con el CSV que sube
 * Secretaria: los de ese anio y envio se reemplazan enteros. Los lee deptos
 * (GET /rest/inscriptos) para las salidas de campo. Solo el DNI: ningun otro
 * dato de las personas.
 *
 * Los textos van en LATIN1, como la base (la API REST ya convierte el JSON).
 * Archivo en ASCII.
 */
class materias_inscripto
{
	const TABLA = 'huayca.materias_inscripto';
	const MAX_FILAS = 30000;

	/** Columna de la tabla => clave de la fila que manda deptos. */
	static $COLUMNAS = array(
		'cod_carrera' => 'cod_carrera',
		'cod_guarani' => 'cod_actividad',
		'plan_siu' => 'plan_siu',
		'materia' => 'materia',
		'dni' => 'dni',
	);

	static function db()
	{
		return toba::db('catedras');
	}

	/**
	 * Las filas que manda deptos -> filas con las columnas de la tabla, sin
	 * repetidas. El DNI, solo digitos. Levanta InvalidArgumentException con el
	 * numero de fila si falta algo.
	 */
	static function normalizar($filas)
	{
		$salida = array();
		$vistas = array();
		foreach (array_values($filas) as $i => $f) {
			$r = array();
			foreach (self::$COLUMNAS as $columna => $campo) {
				$r[$columna] = (is_array($f) && isset($f[$campo]) && !is_array($f[$campo]))
					? materias_equipo::limpio($f[$campo]) : '';
			}
			$r['cod_carrera'] = strtoupper($r['cod_carrera']);
			$r['cod_guarani'] = strtoupper($r['cod_guarani']);
			$r['dni'] = preg_replace('/[^0-9]/', '', $r['dni']);
			foreach (array('cod_carrera', 'materia', 'dni') as $obligatoria) {
				if ($r[$obligatoria] === '') {
					throw new InvalidArgumentException('La fila ' . ($i + 1) . " de los inscriptos no tiene $obligatoria.");
				}
			}
			$k = implode("\x1f", $r);
			if (!isset($vistas[$k])) {
				$vistas[$k] = true;
				$salida[] = $r;
			}
		}
		return $salida;
	}

	/**
	 * Reemplaza los de ese anio y envio, dentro de una transaccion que abre
	 * quien llama. Devuelve array('antes' => filas que habia, 'filas' => n,
	 * 'estudiantes' => DNI distintos, 'materias' => materias distintas).
	 */
	static function aplicar_en($db, $ano, $envio, $filas, $usuario)
	{
		$ano = (int) $ano;
		$envio = (int) $envio;
		$usuario = substr((string) $usuario, 0, 100);
		$q = function ($v) use ($db) {
			return $v === null ? 'NULL' : $db->quote($v);
		};
		$donde = ' WHERE ano_academico = ' . $ano . ' AND envio = ' . $envio;
		$antes = $db->consultar_fila('SELECT count(*) AS n FROM ' . self::TABLA . $donde);
		$db->ejecutar('DELETE FROM ' . self::TABLA . $donde);
		$columnas = array_keys(self::$COLUMNAS);
		$dnis = array();
		$materias = array();
		// De a 500 por INSERT: un envio son miles de filas.
		foreach (array_chunk($filas, 500) as $tanda) {
			$valores = array();
			foreach ($tanda as $f) {
				$v = array((string) $ano, (string) $envio);
				foreach ($columnas as $c) {
					$v[] = $q($f[$c]);
				}
				$v[] = $q($usuario);
				$valores[] = '(' . implode(', ', $v) . ')';
				$dnis[$f['dni']] = true;
				$materias[$f['cod_carrera'] . '|' . $f['cod_guarani'] . '|' . $f['materia']] = true;
			}
			$db->ejecutar('INSERT INTO ' . self::TABLA . ' (ano_academico, envio, ' . implode(', ', $columnas)
				. ', actualizado_por) VALUES ' . implode(', ', $valores));
		}
		$obs = 'Inscriptos ' . $ano . ' envio ' . $envio . ': ' . count($dnis) . ' estudiantes en ' . count($materias)
			. ' materias (' . count($filas) . ' filas; antes ' . (int) $antes['n'] . ')';
		$db->ejecutar('INSERT INTO huayca.movimientos (nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento,
			usuario_movimiento, observaciones, estado_mov) VALUES (' . $q('materias_inscripto') . ', ' . $ano
			. ', now(), ' . $q('Inscriptos') . ', ' . $q($usuario) . ', ' . $q($obs) . ', NULL)');
		return array('antes' => (int) $antes['n'], 'filas' => count($filas), 'estudiantes' => count($dnis),
			'materias' => count($materias));
	}

	/** Reemplaza los del anio y envio en su transaccion; con $simular, lo deshace. */
	static function aplicar($ano, $envio, $filas, $usuario, $simular)
	{
		$db = self::db();
		$db->abrir_transaccion();
		try {
			if (!$simular) {
				$db->ejecutar('LOCK TABLE ' . self::TABLA . ' IN SHARE ROW EXCLUSIVE MODE');
			}
			$r = self::aplicar_en($db, $ano, $envio, $filas, $usuario);
			if ($simular) {
				$db->abortar_transaccion();
			} else {
				$db->cerrar_transaccion();
			}
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('INSCRIPTOS ' . $ano . '/' . $envio . ($simular ? ' (simulado)' : '') . ': ' . $e->getMessage());
			throw $e;
		}
		return $r;
	}

	/**
	 * Las filas de ese anio (y envio, si viene), en UTF-8, con las claves que
	 * manda deptos mas ano_academico y envio.
	 */
	static function filas($ano, $envio = null)
	{
		$where = ' WHERE ano_academico = ' . (int) $ano . ($envio === null ? '' : ' AND envio = ' . (int) $envio);
		$filas = self::db()->consultar('SELECT * FROM ' . self::TABLA . $where
			. ' ORDER BY envio, cod_carrera, materia, dni');
		$salida = array();
		foreach ($filas as $f) {
			$r = array('ano_academico' => (int) $f['ano_academico'], 'envio' => (int) $f['envio']);
			foreach (self::$COLUMNAS as $columna => $campo) {
				$r[$campo] = mb_convert_encoding((string) $f[$columna], 'UTF-8', 'ISO-8859-1');
			}
			$salida[] = $r;
		}
		return $salida;
	}

}
