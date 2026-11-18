<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../administracion/mocovi_anio.php';

/**
 * @description Publicar o despublicar programas (con su planificacion) e informes
 *
 * POST /rest/publicar
 *   {"publicar": true|false, "quien": "Nombre en deptos",
 *    "programas": [id_programa, ...], "informes": [id_informe, ...]}
 *
 * Desde 2027 los documentos que crea el informe de Mocovi nacen sin publicar:
 * el docente no los ve hasta que Secretaria los publica desde deptos
 * (/programas). Despublicar, solo en estado docente. Ver
 * catedras_mocovi::publicar. Solo para el usuario REST USUARIO.
 *
 * Archivo en ASCII.
 */
class recurso_publicar implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_IDS = 5000;

	static function _get_modelos()
	{
		return array();
	}

	static function ids($cuerpo, $clave)
	{
		$v = isset($cuerpo[$clave]) ? $cuerpo[$clave] : array();
		if (!is_array($v) || count($v) > self::MAX_IDS) {
			throw new rest_error(400, "\"$clave\" tiene que ser una lista de ids.");
		}
		$salida = array();
		foreach ($v as $id) {
			if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
				throw new rest_error(400, "\"$clave\" tiene que ser una lista de ids.");
			}
			$salida[] = (int) $id;
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
		if (!is_array($cuerpo) || !isset($cuerpo['publicar']) || !is_bool($cuerpo['publicar'])) {
			throw new rest_error(400, 'Falta "publicar" (true o false).');
		}
		$programas = self::ids($cuerpo, 'programas');
		$informes = self::ids($cuerpo, 'informes');
		$quien = isset($cuerpo['quien']) && !is_array($cuerpo['quien'])
			? substr(preg_replace('/\s+/', ' ', trim((string) $cuerpo['quien'])), 0, 80) : '';
		try {
			$r = catedras_mocovi::publicar($programas, $informes, $cuerpo['publicar'],
				'deptos' . ($quien !== '' ? ': ' . $quien : ''));
		} catch (Exception $e) {
			throw new rest_error(500, 'No se pudo guardar y no se cambio nada.');
		}
		foreach ($r['no_se_tocan'] as $i => $n) {
			$r['no_se_tocan'][$i]['motivo'] = html_entity_decode($n['motivo'], ENT_QUOTES, 'ISO-8859-1');
		}
		rest::response()->get($r);
	}
}
