<?php
require_once dirname(__FILE__) . '/materias_equipo.php';

/**
 * Los dictados conjuntos de cada anio (huayca.materias_conjunto, ver
 * deploy/sql/materias_conjunto.sql): que materias, de que carreras y planes,
 * se dictan juntas. Vienen de Mocovi (la hoja ConjuntosMaterias de la
 * planilla) y los manda deptos (POST /rest/conjuntos): los del anio se
 * reemplazan enteros. Los lee deptos (GET /rest/conjuntos) para las salidas
 * de campo de un conjunto.
 *
 * Los textos van en LATIN1, como la base (la API REST ya convierte el JSON).
 * Archivo en ASCII.
 */
class materias_conjunto
{
	const TABLA = 'huayca.materias_conjunto';
	const MAX_FILAS = 5000;

	/** Columna de la tabla => clave de la fila que manda deptos (las de la hoja). */
	static $COLUMNAS = array(
		'id_conjunto' => 'id_conjunto',
		'conjunto' => 'conjunto',
		'periodo' => 'periodo',
		'materia' => 'materia',
		'cod_guarani' => 'cod_siu',
		'cod_carrera' => 'carrera',
		'plan_mocovi' => 'ordenanza',
	);

	static function db()
	{
		return toba::db('catedras');
	}

	/**
	 * Las filas que manda deptos -> filas con las columnas de la tabla, sin
	 * repetidas. Levanta InvalidArgumentException con el numero de fila si
	 * falta algo.
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
			foreach (array('id_conjunto', 'conjunto', 'materia', 'cod_carrera', 'plan_mocovi') as $obligatoria) {
				if ($r[$obligatoria] === '') {
					throw new InvalidArgumentException('La fila ' . ($i + 1) . " de los conjuntos no tiene $obligatoria.");
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
	 * Reemplaza los conjuntos de ese anio, dentro de una transaccion que abre
	 * quien llama. Devuelve array('antes' => filas que habia, 'filas' => n,
	 * 'conjuntos' => n, 'sin_materia' => filas que no estan en el catalogo).
	 */
	static function aplicar_en($db, $ano, $filas, $usuario)
	{
		$ano = (int) $ano;
		$usuario = substr((string) $usuario, 0, 100);
		$q = function ($v) use ($db) {
			return $v === null ? 'NULL' : $db->quote($v);
		};
		$antes = $db->consultar_fila('SELECT count(*) AS n FROM ' . self::TABLA . ' WHERE ano_academico = ' . $ano);
		$indice = materias_equipo::indice_materias();
		$db->ejecutar('DELETE FROM ' . self::TABLA . ' WHERE ano_academico = ' . $ano);
		$columnas = array_keys(self::$COLUMNAS);
		$sin_materia = array();
		$conjuntos = array();
		foreach ($filas as $f) {
			$km = $f['cod_guarani'] . '|' . $f['cod_carrera'] . '|' . $f['plan_mocovi'];
			$id = isset($indice[$km]) ? $indice[$km] : null;
			if ($id === null) {
				$sin_materia[] = $f;
			}
			$conjuntos[$f['id_conjunto']] = true;
			$valores = array((string) $ano);
			foreach ($columnas as $c) {
				$valores[] = $q($f[$c]);
			}
			$valores[] = $id === null ? 'NULL' : (string) (int) $id;
			$valores[] = $q($usuario);
			$db->ejecutar('INSERT INTO ' . self::TABLA . ' (ano_academico, ' . implode(', ', $columnas)
				. ', id_materia, actualizado_por) VALUES (' . implode(', ', $valores) . ')');
		}
		$obs = 'Conjuntos ' . $ano . ': ' . count($conjuntos) . ' dictados, ' . count($filas) . ' materias (antes ' . (int) $antes['n'] . ')';
		$db->ejecutar('INSERT INTO huayca.movimientos (nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento,
			usuario_movimiento, observaciones, estado_mov) VALUES (' . $q('materias_conjunto') . ', ' . $ano
			. ', now(), ' . $q('Conjuntos de Mocovi') . ', ' . $q($usuario) . ', ' . $q($obs) . ', NULL)');
		return array('antes' => (int) $antes['n'], 'filas' => count($filas), 'conjuntos' => count($conjuntos),
			'sin_materia' => $sin_materia);
	}

	/** Reemplaza los del anio en su transaccion; con $simular, lo deshace. */
	static function aplicar($ano, $filas, $usuario, $simular)
	{
		$db = self::db();
		$db->abrir_transaccion();
		try {
			if (!$simular) {
				$db->ejecutar('LOCK TABLE ' . self::TABLA . ' IN SHARE ROW EXCLUSIVE MODE');
			}
			$r = self::aplicar_en($db, $ano, $filas, $usuario);
			if ($simular) {
				$db->abortar_transaccion();
			} else {
				$db->cerrar_transaccion();
			}
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('CONJUNTOS ' . $ano . ($simular ? ' (simulado)' : '') . ': ' . $e->getMessage());
			throw $e;
		}
		return $r;
	}

	/**
	 * Las filas de ese anio (o de todos, con null), en UTF-8, con las claves
	 * de la hoja: id_conjunto, conjunto, periodo, materia, cod_siu, carrera,
	 * ordenanza, mas ano_academico e id_materia.
	 */
	static function filas($ano = null)
	{
		$where = $ano === null ? '' : ' WHERE ano_academico = ' . (int) $ano;
		$filas = self::db()->consultar('SELECT * FROM ' . self::TABLA . $where
			. ' ORDER BY ano_academico, id_conjunto, id_materia_conjunto');
		$salida = array();
		foreach ($filas as $f) {
			$r = array('ano_academico' => (int) $f['ano_academico'],
				'id_materia' => $f['id_materia'] === null ? null : (int) $f['id_materia']);
			foreach (self::$COLUMNAS as $columna => $campo) {
				$r[$campo] = mb_convert_encoding((string) $f[$columna], 'UTF-8', 'ISO-8859-1');
			}
			$salida[] = $r;
		}
		return $salida;
	}
}
