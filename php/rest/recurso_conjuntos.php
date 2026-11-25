<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../informes/materias_conjunto.php';

/**
 * @description Los dictados conjuntos de cada anio (de Mocovi)
 *
 * GET /rest/conjuntos[?ano_academico=2027]
 *   Una fila por dictado y materia: {ano_academico, id_conjunto, conjunto,
 *   periodo, materia, cod_siu, carrera, ordenanza, id_materia}. Sin anio, los
 *   de todos los anios.
 *
 * POST /rest/conjuntos
 *   {"simular": true|false, "quien": "Nombre en deptos", "ano_academico": "2027",
 *    "conjuntos": [{"id_conjunto", "conjunto", "periodo", "materia", "cod_siu",
 *                   "carrera", "ordenanza"}, ...]}
 *   Reemplaza los del anio, en una transaccion. Devuelve {simulado,
 *   ano_academico, antes, filas, conjuntos, sin_materia: [filas]}. Solo para
 *   el usuario de alta de deptos (como /rest/mocovi-anio).
 *
 * Lo usa deptos (catedras.py: traer_conjuntos y guardar_conjuntos).
 * Archivo en ASCII.
 */
class recurso_conjuntos implements modelable
{
	const USUARIO = 'deptos_alta';

	static function _get_modelos()
	{
		return array();
	}

	static function get_list()
	{
		$ano = trim((string) ($_GET['ano_academico'] ?? ''));
		if ($ano !== '' && !preg_match('/^20[0-9]{2}$/', $ano)) {
			throw new rest_error(400, 'ano_academico tiene que ser un anio (AAAA).');
		}
		rest::response()->get(materias_conjunto::filas($ano === '' ? null : (int) $ano));
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
		$ano = isset($cuerpo['ano_academico']) ? trim((string) $cuerpo['ano_academico']) : '';
		if (!preg_match('/^20[0-9]{2}$/', $ano)) {
			throw new rest_error(400, 'Falta "ano_academico" o no es un anio.');
		}
		$filas = isset($cuerpo['conjuntos']) ? $cuerpo['conjuntos'] : null;
		if (!is_array($filas) || !$filas) {
			throw new rest_error(400, 'Faltan los conjuntos ("conjuntos").');
		}
		if (count($filas) > materias_conjunto::MAX_FILAS) {
			throw new rest_error(400, 'Demasiadas filas en "conjuntos".');
		}
		try {
			$filas = materias_conjunto::normalizar($filas);
		} catch (InvalidArgumentException $e) {
			throw new rest_error(400, $e->getMessage());
		}
		$simular = !empty($cuerpo['simular']);
		$quien = isset($cuerpo['quien']) ? substr(preg_replace('/\s+/', ' ', trim((string) $cuerpo['quien'])), 0, 80) : '';
		$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
		try {
			$r = materias_conjunto::aplicar((int) $ano, $filas, $usuario, $simular);
		} catch (Exception $e) {
			throw new rest_error(500, 'No se pudo ' . ($simular ? 'simular' : 'guardar') . ' y no se cambio nada.');
		}
		$sin = array();
		foreach ($r['sin_materia'] as $f) {
			$fila = array();
			foreach (materias_conjunto::$COLUMNAS as $columna => $campo) {
				$fila[$campo] = mb_convert_encoding((string) $f[$columna], 'UTF-8', 'ISO-8859-1');
			}
			$sin[] = $fila;
		}
		rest::response()->get(array('simulado' => $simular, 'ano_academico' => (int) $ano, 'antes' => $r['antes'],
			'filas' => $r['filas'], 'conjuntos' => $r['conjuntos'], 'sin_materia' => $sin));
	}
}
