<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../administracion/materias_alta.php';

/**
 * @description Alta y modificacion de materias (huayca.materias)
 *
 * POST /rest/materias-alta (con guion: el ruteador de Toba no acepta "_" en la
 * URL y pasa los "-" a "_" para encontrar este archivo)
 *   {"simular": true|false, "quien": "Nombre en deptos",
 *    "materias": [{"linea", "nombre_carrera", "nombre_materia", "ano_plan", ...,
 *                  "cod_guarani", "plan_guarani", "version_guarani", ...}, ...],
 *    "campos": ["periodo_plan", ...],              (opcional: solo esos se modifican)
 *    "campos_por_linea": {"5": ["periodo_plan"], ...}}  (opcional: lo elegido en
 *                                                     cada fila; le gana a "campos")
 *
 * Lo usa deptos (/programas/materias): Secretaria sube un CSV con las columnas
 * de la tabla. La materia es la misma si coinciden cod_guarani, plan_guarani y
 * version_guarani: si no esta se da de alta, si esta se modifican los campos
 * que vienen con valor. Ver php/administracion/materias_alta.php.
 *
 * Devuelve {"simulado", "altas", "modificaciones", "sin_cambios", "problemas",
 * "campos_con_cambios"}.
 * Con "simular" no escribe nada. Solo para el usuario REST USUARIO
 * (servidor_usuarios.ini): el que usa deptos para leer no puede escribir.
 *
 * Archivo en ASCII.
 */
class recurso_materias_alta implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_FILAS = 2000;

	static function _get_modelos()
	{
		return array();
	}

	/** Una lista de columnas de materias, o 400. */
	static function columnas($lista, $donde)
	{
		if (!is_array($lista)) {
			throw new rest_error(400, "$donde tiene que ser una lista de columnas.");
		}
		$salida = array();
		foreach ($lista as $c) {
			// array_key_exists: las columnas text tienen largo null.
			if (!is_string($c) || !array_key_exists($c, catedras_materias::$COLUMNAS)) {
				throw new rest_error(400, "En $donde hay una columna que no es de materias.");
			}
			$salida[] = $c;
		}
		return $salida;
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
		$filas = isset($cuerpo['materias']) ? $cuerpo['materias'] : null;
		if (!is_array($filas) || !$filas) {
			throw new rest_error(400, 'Faltan las materias ("materias").');
		}
		if (count($filas) > self::MAX_FILAS) {
			throw new rest_error(400, 'Demasiadas filas en "materias".');
		}
		foreach (array_values($filas) as $i => $f) {
			if (!is_array($f)) {
				throw new rest_error(400, 'La fila ' . ($i + 1) . ' de "materias" no es un objeto.');
			}
		}
		$campos = isset($cuerpo['campos']) ? self::columnas($cuerpo['campos'], '"campos"') : null;
		$por_linea = null;
		if (isset($cuerpo['campos_por_linea'])) {
			if (!is_array($cuerpo['campos_por_linea'])) {
				throw new rest_error(400, '"campos_por_linea" tiene que ser un objeto {linea: [columnas]}.');
			}
			$por_linea = array();
			foreach ($cuerpo['campos_por_linea'] as $linea => $lista) {
				if (!ctype_digit((string) $linea)) {
					throw new rest_error(400, 'En "campos_por_linea" hay una linea que no es un numero.');
				}
				$por_linea[(int) $linea] = self::columnas($lista, 'la linea ' . $linea . ' de "campos_por_linea"');
			}
		}
		$simular = !empty($cuerpo['simular']);
		$quien = (isset($cuerpo['quien']) && !is_array($cuerpo['quien']))
			? substr(preg_replace('/\s+/', ' ', trim((string) $cuerpo['quien'])), 0, 80) : '';
		$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
		try {
			$r = catedras_materias::aplicar(catedras_materias::normalizar($filas), $usuario, $simular, $campos,
				$por_linea);
		} catch (Exception $e) {
			throw new rest_error(500, 'No se pudo ' . ($simular ? 'simular' : 'guardar') . ' y no se cambio nada.');
		}
		rest::response()->get(array('simulado' => $simular) + $r);
	}
}
