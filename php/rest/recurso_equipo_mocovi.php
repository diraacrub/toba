<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../informes/materias_equipo.php';

/**
 * @description El equipo de cada materia del anio, desde el informe de Mocovi
 *
 * POST /rest/equipo-mocovi (con guion: el ruteador de Toba no acepta "_" en la
 * URL y pasa los "-" a "_" para encontrar este archivo)
 *   {"ano_academico": "2027", "simular": true|false, "quien": "Nombre en deptos",
 *    "filas": [{"materia": "...", "cod_siu": "PB109", "carrera": "PBIB",
 *               "ordenanza": "0750/1820", "docente": "APELLIDO, NOMBRE",
 *               "legajo": "57390", "categoria": "JTP3", "desig": "53241",
 *               "modulo": "modulo 1", "rol": "Aux", "periodo": "2CUAT"}, ...]}
 *
 * Las filas son las del informe de Mocovi que Secretaria sube en deptos
 * (/programas), completo. Sincroniza huayca.materias_equipo de ese anio (ver
 * php/informes/materias_equipo.php): agrega lo nuevo, borra lo que ya no
 * viene, reactiva lo borrado que vuelve igual.
 *
 * Con "simular" no guarda nada: devuelve lo que pasaria. Sin "simular" vuelve a
 * planificar con la tabla bloqueada y escribe en una transaccion. Mandar lo
 * mismo dos veces no cambia nada la segunda.
 *
 * Solo para el usuario REST USUARIO (servidor_usuarios.ini), el mismo del alta
 * de programas: el que usa deptos para leer no puede escribir.
 *
 * Archivo en ASCII.
 */
class recurso_equipo_mocovi implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_FILAS = 5000;
	// Cuantas filas de cada lista se devuelven con detalle (los totales van siempre).
	const MAX_DETALLE = 2000;

	static function _get_modelos()
	{
		return array();
	}

	static function fila_salida($f)
	{
		$r = array();
		foreach (array_keys(materias_equipo::$COLUMNAS) as $c) {
			$r[$c] = isset($f[$c]) ? $f[$c] : '';
		}
		$r['id_materia'] = (isset($f['id_materia']) && $f['id_materia'] !== null && $f['id_materia'] !== '')
			? (int) $f['id_materia'] : null;
		if (isset($f['id_materia_equipo'])) {
			$r['id_materia_equipo'] = (int) $f['id_materia_equipo'];
		}
		return $r;
	}

	static function post_list()
	{
		$u = rest::usuario();
		if (!$u || $u->get_usuario() !== self::USUARIO) {
			throw new rest_error(403, 'Este recurso es solo para el usuario de alta de deptos.');
		}
		$cuerpo = rest::request()->get_body_json();
		if (!is_array($cuerpo) || !isset($cuerpo['filas']) || !is_array($cuerpo['filas'])) {
			throw new rest_error(400, 'Falta "filas".');
		}
		$ano = isset($cuerpo['ano_academico']) && !is_array($cuerpo['ano_academico'])
			? trim((string) $cuerpo['ano_academico']) : '';
		if (!preg_match('/^20[0-9]{2}$/', $ano)) {
			throw new rest_error(400, 'Falta "ano_academico" o no es un anio.');
		}
		if (empty($cuerpo['filas'])) {
			throw new rest_error(400, 'El informe no tiene filas.');
		}
		if (count($cuerpo['filas']) > self::MAX_FILAS) {
			throw new rest_error(400, 'Demasiadas filas.');
		}
		try {
			$filas = materias_equipo::normalizar($cuerpo['filas']);
		} catch (InvalidArgumentException $e) {
			throw new rest_error(400, $e->getMessage());
		}
		$simular = !empty($cuerpo['simular']);
		if ($simular) {
			$plan = materias_equipo::planificar($ano, $filas);
		} else {
			$quien = isset($cuerpo['quien']) && !is_array($cuerpo['quien'])
				? substr(preg_replace('/\s+/', ' ', trim((string) $cuerpo['quien'])), 0, 80) : '';
			$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
			try {
				$plan = materias_equipo::aplicar($ano, $filas, $usuario);
			} catch (Exception $e) {
				toba::logger()->error('equipo-mocovi: ' . $e->getMessage());
				throw new rest_error(500, 'No se pudo guardar y no se cambio nada.');
			}
		}
		$salida = array('simulado' => $simular, 'ano_academico' => (int) $ano, 'filas' => count($filas),
			'iguales' => $plan['iguales'], 'repetidas' => $plan['repetidas']);
		foreach (array('agregar', 'reactivar', 'borrar', 'sin_materia') as $k) {
			$salida['n_' . $k] = count($plan[$k]);
			$salida[$k] = array_map(array('recurso_equipo_mocovi', 'fila_salida'),
				array_slice($plan[$k], 0, self::MAX_DETALLE));
		}
		rest::response()->get($salida);
	}
}
