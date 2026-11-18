<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../administracion/actualizar_catedras.php';

/**
 * @description Los informes de catedra: crear los que faltan y su responsable, desde Mocovi
 *
 * POST /rest/actualizar-informes (con guion: el ruteador de Toba no acepta "_")
 *   {"simular": true|false, "quien": "Nombre en deptos",
 *    "filas": [{"id_programa": 900, "legajo_resp": "57390", "id_designacion": "53241",
 *               "cargo_resp": "PAD3", "apellido_resp": "...", "nombre_resp": "...",
 *               "equipo_catedra": "[[...]]", "vigente": true, "materia": "..."}, ...]}
 *
 * Lo usa deptos (/programas): con el informe de Mocovi del anio arma el
 * responsable y el equipo de cada programa que ya existe, y "vigente" dice si
 * el plazo sigue abierto segun el calendario academico. Solo si: informe en estado docente y plazo del informe vigente; los que faltan se crean.
 * Ver php/administracion/actualizar_catedras.php. Solo desde catedras_actualizar::DESDE.
 *
 * Con "simular" no guarda nada. Solo para el usuario REST USUARIO
 * (servidor_usuarios.ini), el del alta de programas.
 *
 * Archivo en ASCII.
 */
class recurso_actualizar_informes implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_FILAS = 3000;

	static function _get_modelos()
	{
		return array();
	}

	static function plano($html)
	{
		return html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'ISO-8859-1');
	}

	static function salida($p)
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
		if (isset($p['motivo'])) {
			$r['motivo'] = self::plano($p['motivo']);
		}
		if (isset($p['motivos'])) {
			$r['motivos'] = array_map(array('recurso_actualizar_informes', 'plano'), $p['motivos']);
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
		if (count($cuerpo['filas']) > self::MAX_FILAS) {
			throw new rest_error(400, 'Demasiadas filas.');
		}
		try {
			$filas = catedras_actualizar::normalizar($cuerpo['filas']);
		} catch (InvalidArgumentException $e) {
			throw new rest_error(400, $e->getMessage());
		}
		$simular = !empty($cuerpo['simular']);
		if ($simular) {
			$plan = catedras_actualizar::planificar_informes($filas);
		} else {
			$quien = substr(catedras_actualizar::texto($cuerpo, 'quien'), 0, 80);
			try {
				$plan = catedras_actualizar::aplicar_informes($filas, 'deptos' . ($quien !== '' ? ': ' . $quien : ''));
			} catch (Exception $e) {
				throw new rest_error(500, 'No se pudo guardar y no se cambio nada.');
			}
		}
		$salida = array('simulado' => $simular);
		foreach (array('crear', 'actualizar', 'sin_cambios', 'no_se_tocan', 'problemas') as $k) {
			$salida[$k] = array_map(array('recurso_actualizar_informes', 'salida'), $plan[$k]);
		}
		if (isset($plan['creados'])) {
			$salida['creados'] = $plan['creados'];
		}
		rest::response()->get($salida);
	}
}
