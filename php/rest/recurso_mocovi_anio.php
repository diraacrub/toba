<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../administracion/mocovi_anio.php';

/**
 * @description El informe de Mocovi de un anio, en una sola pasada
 *
 * POST /rest/mocovi-anio (con guion: el ruteador de Toba no acepta "_" en la
 * URL y pasa los "-" a "_" para encontrar este archivo)
 *   {"simular": true|false, "quien": "Nombre en deptos", "ano_academico": "2027",
 *    "equipo": [{"materia", "cod_siu", "carrera", "ordenanza", "docente", "legajo",
 *                "categoria", "desig", "modulo", "rol", "periodo"}, ...],
 *    "alta": [{"ano_academico", "id_materia", "periodo_dictado", "legajo_resp",
 *              "id_designacion", "cargo_resp", "apellido_resp", "nombre_resp",
 *              "equipo_catedra", "id_programa_anterior", "materia"}, ...],
 *    "programas": [{"id_programa", "legajo_resp", "id_designacion", "cargo_resp",
 *                   "apellido_resp", "nombre_resp", "equipo_catedra", "vigente", "materia"}, ...],
 *    "informes": [{"id_programa", "legajo_resp", ..., "vigente", "materia"}, ...]}
 *
 * Lo usa deptos (/programas): el equipo de cada materia, el alta de lo que
 * falta y la actualizacion de lo que ya existe, en una transaccion. Ver
 * php/administracion/mocovi_anio.php. "vigente" dice si el plazo de ese
 * documento sigue abierto segun el calendario academico de deptos.
 *
 * Con "simular" hace todo y lo deshace: devuelve lo que pasaria. Solo para el
 * usuario REST USUARIO (servidor_usuarios.ini): el que usa deptos para leer no
 * puede escribir.
 *
 * Archivo en ASCII.
 */
class recurso_mocovi_anio implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_FILAS = 5000;
	// Cuantas filas de cada lista del equipo se devuelven con detalle (los totales van siempre).
	const MAX_DETALLE = 2000;

	static function _get_modelos()
	{
		return array();
	}

	/** Un motivo (HTML con entidades), plano. */
	static function plano($html)
	{
		return html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'ISO-8859-1');
	}

	static function texto($fila, $campo)
	{
		return (isset($fila[$campo]) && !is_array($fila[$campo])) ? trim((string) $fila[$campo]) : '';
	}

	static function lista($cuerpo, $clave)
	{
		$v = isset($cuerpo[$clave]) ? $cuerpo[$clave] : array();
		if (!is_array($v)) {
			throw new rest_error(400, "\"$clave\" tiene que ser una lista.");
		}
		if (count($v) > self::MAX_FILAS) {
			throw new rest_error(400, "Demasiadas filas en \"$clave\".");
		}
		foreach ($v as $i => $f) {
			if (!is_array($f)) {
				throw new rest_error(400, "La fila " . ($i + 1) . " de \"$clave\" no es un objeto.");
			}
		}
		return array_values($v);
	}

	/** Las filas del alta como las usa catedras_importar_programas. */
	static function alta($filas)
	{
		$salida = array();
		foreach ($filas as $i => $c) {
			$salida[] = array(
				'linea' => $i + 1,
				'ano' => self::texto($c, 'ano_academico'),
				'id_materia' => self::texto($c, 'id_materia'),
				'periodo' => self::texto($c, 'periodo_dictado'),
				'legajo' => self::texto($c, 'legajo_resp'),
				'id_designacion' => self::texto($c, 'id_designacion'),
				'cargo' => self::texto($c, 'cargo_resp'),
				'apellido' => self::texto($c, 'apellido_resp'),
				'nombre' => self::texto($c, 'nombre_resp'),
				'equipo' => self::texto($c, 'equipo_catedra'),
				'anterior' => self::texto($c, 'id_programa_anterior'),
				'materia' => self::texto($c, 'materia'),
			);
		}
		return $salida;
	}

	static function fila_alta($p)
	{
		$f = $p['fila'];
		$r = array('linea' => (int) $f['linea'], 'id_materia' => (int) $f['id_materia'], 'periodo_dictado' => $f['periodo'],
			'legajo_resp' => $f['legajo'], 'carrera' => isset($f['carrera']) ? $f['carrera'] : '', 'materia' => $f['materia'],
			'id_programa_anterior' => $f['anterior'] !== '' ? (int) $f['anterior'] : null);
		if (isset($p['id'])) {
			$r['id_programa'] = (int) $p['id'];
		}
		if (isset($p['falta'])) {
			$r['falta'] = $p['falta'];
		}
		if (isset($p['motivos'])) {
			$r['motivos'] = array_map(array('recurso_mocovi_anio', 'plano'), $p['motivos']);
		}
		return $r;
	}

	/** Una fila de programas, vigente o informes. */
	static function fila($p)
	{
		$f = $p['fila'];
		$r = array('linea' => (int) $f['linea'], 'id_programa' => (int) $p['id'], 'materia' => $f['materia']);
		if (isset($p['id_informe'])) {
			$r['id_informe'] = (int) $p['id_informe'];
		}
		if (isset($p['cambios'])) {
			$r['cambios'] = array();
			foreach ($p['cambios'] as $k => $ad) {
				$r['cambios'][$k] = array('antes' => $ad[0], 'despues' => $ad[1]);
			}
		}
		foreach (array('antes', 'despues') as $k) {
			if (isset($p[$k])) {
				$r[$k] = array();
				foreach (catedras_actualizar::$RESP as $c) {
					$v = $p[$k][$c] ?? null;
					$r[$k][$c] = in_array($c, array('legajo_resp', 'dni_resp', 'id_designacion'))
						? catedras_actualizar::entero($v) : trim((string) $v);
				}
			}
		}
		foreach (array('id_materia', 'periodo') as $k) {
			if (isset($p[$k])) {
				$r[$k] = $p[$k];
			}
		}
		if (isset($p['motivo'])) {
			$r['motivo'] = self::plano($p['motivo']);
		}
		if (isset($p['motivos'])) {
			$r['motivos'] = array_map(array('recurso_mocovi_anio', 'plano'), $p['motivos']);
		}
		return $r;
	}

	static function fila_equipo($f)
	{
		$r = array();
		foreach (array_keys(materias_equipo::$COLUMNAS) as $c) {
			$r[$c] = isset($f[$c]) ? $f[$c] : '';
		}
		$r['id_materia'] = (isset($f['id_materia']) && $f['id_materia'] !== null && $f['id_materia'] !== '')
			? (int) $f['id_materia'] : null;
		return $r;
	}

	static function post_list()
	{
		$u = rest::usuario();
		if (!$u || $u->get_usuario() !== self::USUARIO) {
			throw new rest_error(403, 'Este recurso es solo para el usuario de alta de deptos.');
		}
		$cuerpo = rest::request()->get_body_json();
		if (!is_array($cuerpo)) {
			throw new rest_error(400, 'El cuerpo tiene que ser un objeto JSON.');
		}
		$ano = self::texto($cuerpo, 'ano_academico');
		if (!preg_match('/^20[0-9]{2}$/', $ano)) {
			throw new rest_error(400, 'Falta "ano_academico" o no es un anio.');
		}
		$equipo = self::lista($cuerpo, 'equipo');
		if (!$equipo) {
			throw new rest_error(400, 'Falta el informe de Mocovi ("equipo").');
		}
		try {
			$equipo = materias_equipo::normalizar($equipo);
			$programas = catedras_actualizar::normalizar(self::lista($cuerpo, 'programas'));
			$informes = catedras_actualizar::normalizar(self::lista($cuerpo, 'informes'));
		} catch (InvalidArgumentException $e) {
			throw new rest_error(400, $e->getMessage());
		}
		$alta = self::alta(self::lista($cuerpo, 'alta'));
		foreach ($alta as $f) {
			if ($f['ano'] !== $ano) {
				throw new rest_error(400, 'La fila ' . $f['linea'] . ' del alta es de otro anio.');
			}
		}
		$simular = !empty($cuerpo['simular']);
		$quien = substr(preg_replace('/\s+/', ' ', self::texto($cuerpo, 'quien')), 0, 80);
		$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
		try {
			$r = catedras_mocovi::aplicar((int) $ano, $equipo, $alta, $programas, $informes, $usuario, $simular);
		} catch (Exception $e) {
			throw new rest_error(500, 'No se pudo ' . ($simular ? 'simular' : 'guardar') . ' y no se cambio nada.');
		}

		$e = $r['equipo'];
		$salida = array('simulado' => $simular, 'ano_academico' => (int) $ano,
			'equipo' => array('filas' => count($equipo), 'iguales' => $e['iguales'], 'repetidas' => $e['repetidas']));
		foreach (array('agregar', 'reactivar', 'borrar', 'sin_materia') as $k) {
			$salida['equipo']['n_' . $k] = count($e[$k]);
			$salida['equipo'][$k] = array_map(array('recurso_mocovi_anio', 'fila_equipo'),
				array_slice($e[$k], 0, self::MAX_DETALLE));
		}
		$salida['alta'] = array('creados' => $r['alta']['creados']);
		foreach (array('nuevos', 'incompletos', 'existen', 'problemas') as $k) {
			$salida['alta'][$k] = array_map(array('recurso_mocovi_anio', 'fila_alta'), $r['alta'][$k]);
		}
		foreach (array('programas' => array('actualizar', 'sin_cambios', 'no_se_tocan', 'problemas'),
			'vigente' => array('cambia', 'sin_cambios', 'no_se_tocan', 'problemas'),
			'informes' => array('crear', 'actualizar', 'sin_cambios', 'no_se_tocan', 'problemas')) as $parte => $listas) {
			$salida[$parte] = array();
			foreach ($listas as $k) {
				$salida[$parte][$k] = array_map(array('recurso_mocovi_anio', 'fila'), $r[$parte][$k]);
			}
		}
		$salida['informes']['creados'] = $r['informes']['creados'];
		$salida['vigentes'] = $r['vigentes'];
		$salida['vigentes_cambios'] = $r['vigentes_cambios'];
		rest::response()->get($salida);
	}
}
