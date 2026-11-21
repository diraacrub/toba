<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once dirname(__FILE__) . '/../informes/materias_inscripto.php';

/**
 * @description Los inscriptos de cada materia por anio y envio (solo DNI)
 *
 * GET /rest/inscriptos?ano_academico=2027[&envio=1|2]
 *   Una fila por estudiante y materia: {ano_academico, envio, cod_carrera,
 *   cod_actividad, plan_siu, materia, dni}.
 *
 * POST /rest/inscriptos
 *   {"simular": true|false, "quien": "Nombre en deptos", "ano_academico": "2027",
 *    "envio": 1, "inscriptos": [{"cod_carrera", "cod_actividad", "plan_siu",
 *                                "materia", "dni"}, ...]}
 *   Reemplaza los de ese anio y envio, en una transaccion. Devuelve {simulado,
 *   ano_academico, envio, antes, filas, estudiantes, materias}. Solo para el
 *   usuario de alta de deptos (como /rest/mocovi-anio).
 *
 * El envio 1 son las materias del 1.er cuatrimestre y las anuales; el 2, las
 * del 2.o cuatrimestre. Lo usa deptos (catedras.py: enviar_inscriptos y
 * traer_inscriptos). Archivo en ASCII.
 */
class recurso_inscriptos implements modelable
{
	const USUARIO = 'deptos_alta';

	static function _get_modelos()
	{
		return array();
	}

	static function envio($valor, $obligatorio)
	{
		$e = trim((string) $valor);
		if ($e === '' && !$obligatorio) {
			return null;
		}
		if ($e !== '1' && $e !== '2') {
			throw new rest_error(400, 'El envio tiene que ser 1 o 2.');
		}
		return (int) $e;
	}

	static function get_list()
	{
		$ano = trim((string) ($_GET['ano_academico'] ?? ''));
		if (!preg_match('/^20[0-9]{2}$/', $ano)) {
			throw new rest_error(400, 'Falta ano_academico (AAAA).');
		}
		$envio = self::envio($_GET['envio'] ?? '', false);
		rest::response()->get(materias_inscripto::filas((int) $ano, $envio));
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
		$envio = self::envio($cuerpo['envio'] ?? '', true);
		$filas = isset($cuerpo['inscriptos']) ? $cuerpo['inscriptos'] : null;
		if (!is_array($filas) || !$filas) {
			throw new rest_error(400, 'Faltan los inscriptos ("inscriptos").');
		}
		if (count($filas) > materias_inscripto::MAX_FILAS) {
			throw new rest_error(400, 'Demasiadas filas en "inscriptos".');
		}
		try {
			$filas = materias_inscripto::normalizar($filas);
		} catch (InvalidArgumentException $e) {
			throw new rest_error(400, $e->getMessage());
		}
		$simular = !empty($cuerpo['simular']);
		$quien = isset($cuerpo['quien']) ? substr(preg_replace('/\s+/', ' ', trim((string) $cuerpo['quien'])), 0, 80) : '';
		$usuario = 'deptos' . ($quien !== '' ? ': ' . $quien : '');
		try {
			$r = materias_inscripto::aplicar((int) $ano, $envio, $filas, $usuario, $simular);
		} catch (Exception $e) {
			throw new rest_error(500, 'No se pudo ' . ($simular ? 'simular' : 'guardar') . ' y no se cambio nada.');
		}
		rest::response()->get(array('simulado' => $simular, 'ano_academico' => (int) $ano, 'envio' => $envio,
			'antes' => $r['antes'], 'filas' => $r['filas'], 'estudiantes' => $r['estudiantes'], 'materias' => $r['materias']));
	}
}
