<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

if (!defined('CATEDRAS_IMPORTAR_PROGRAMAS_SIN_EJECUTAR')) {
	define('CATEDRAS_IMPORTAR_PROGRAMAS_SIN_EJECUTAR', true);
}
require_once dirname(__FILE__) . '/../administracion/importar_programas.php';

/**
 * @description Alta de los programas del anio, con su planificacion y su informe
 *
 * POST /rest/alta-programas (con guion: el ruteador de Toba no acepta "_" en la
 * URL y pasa los "-" a "_" para encontrar este archivo)
 *   {"simular": true|false, "quien": "Nombre de quien lo hace en deptos",
 *    "filas": [{"ano_academico": "2027", "id_materia": 1, "periodo_dictado": "1CUAT",
 *               "legajo_resp": "57390", "id_designacion": "53241", "cargo_resp": "PAD3",
 *               "apellido_resp": "...", "nombre_resp": "...", "equipo_catedra": "[[...]]",
 *               "id_programa_anterior": 377, "materia": "..."}, ...]}
 *
 * Lo usa deptos (/programas): Secretaria sube ahi el informe de Mocovi, ve la
 * propuesta, resuelve los dobles y crea, sin bajar ni subir un CSV. Las filas
 * son las mismas columnas del CSV de Administracion > Importar programas, y las
 * reglas son las de esa pantalla (catedras_importar_programas): el DNI sale del
 * usuario de ese legajo, el contenido se copia del programa anterior, cada
 * programa nuevo lleva su planificacion y su informe vacios, lo que existe no
 * se toca (si le falta la planificacion o el informe, se le crea), ids
 * siguientes al mas alto.
 *
 * Con "simular" no guarda nada: devuelve lo que pasaria. Sin "simular" vuelve a
 * planificar contra la base y crea todo en una transaccion; devuelve lo creado.
 * Es idempotente: mandar lo mismo dos veces no crea nada la segunda.
 *
 * Solo para el usuario REST USUARIO (servidor_usuarios.ini): el que usa deptos
 * para leer no puede escribir.
 *
 * Archivo en ASCII.
 */
class recurso_alta_programas implements modelable
{
	const USUARIO = 'deptos_alta';
	const MAX_FILAS = 3000;

	static function _get_modelos()
	{
		return array();
	}

	/** El texto de un motivo del importador (HTML con entidades), plano. */
	static function plano($html)
	{
		return html_entity_decode(strip_tags($html), ENT_QUOTES, 'ISO-8859-1');
	}

	static function fila_salida($p)
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
			$r['motivos'] = array_map(array('recurso_alta_programas', 'plano'), $p['motivos']);
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
		$t = function ($fila, $campo) {
			return (isset($fila[$campo]) && !is_array($fila[$campo])) ? trim((string) $fila[$campo]) : '';
		};
		$filas = array();
		foreach (array_values($cuerpo['filas']) as $i => $c) {
			if (!is_array($c)) {
				throw new rest_error(400, 'La fila ' . ($i + 1) . ' no es un objeto.');
			}
			$filas[] = array(
				'linea' => $i + 1,
				'ano' => $t($c, 'ano_academico'),
				'id_materia' => $t($c, 'id_materia'),
				'periodo' => $t($c, 'periodo_dictado'),
				'legajo' => $t($c, 'legajo_resp'),
				'id_designacion' => $t($c, 'id_designacion'),
				'cargo' => $t($c, 'cargo_resp'),
				'apellido' => $t($c, 'apellido_resp'),
				'nombre' => $t($c, 'nombre_resp'),
				'equipo' => $t($c, 'equipo_catedra'),
				'anterior' => $t($c, 'id_programa_anterior'),
				'materia' => $t($c, 'materia'),
			);
		}
		$plan = catedras_importar_programas::planificar($filas);
		$salida = array('simulado' => !empty($cuerpo['simular']));
		foreach (array('nuevos', 'incompletos', 'existen', 'problemas') as $k) {
			$salida[$k] = array_map(array('recurso_alta_programas', 'fila_salida'), $plan[$k]);
		}
		if (empty($cuerpo['simular'])) {
			$quien = substr(preg_replace('/\s+/', ' ', $t($cuerpo, 'quien')), 0, 80);
			$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
			try {
				$salida['creados'] = catedras_importar_programas::aplicar($plan, $usuario);
			} catch (Exception $e) {
				throw new rest_error(500, 'No se pudo guardar y no se creo nada.');
			}
		}
		rest::response()->get($salida);
	}
}
