<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

/**
 * @description Que materia tiene asignada cada responsable, por anio academico
 *
 * GET /rest/asignaciones?ano_academico=2027[&dni=12345678]
 *
 * Una fila por programa: la asignacion que hace Secretaria al dar de alta los
 * programas del anio. A diferencia de /rest/programas trae los programas en
 * cualquier estado (el estado es el del documento, no el de la asignacion) y
 * solo lo necesario para saber quien dicta que, sin el contenido.
 *
 * Lo usa deptos (salidas de campo): el docente elige entre las materias de las
 * que es responsable ese anio. id_materia es el mismo de /rest/materias.
 *
 * dni_resp y legajo_resp son los del programa (quien lo firma); dni_vigente y
 * legajo_vigente, los del responsable vigente (programas.*_resp_vig, el que
 * dice Mocovi hoy; igual al del programa si no cambio). Con &dni= se filtra
 * por el vigente.
 *
 * Los programas ocultos (observaciones con "[ocultar]", como en dt_programas)
 * no cuentan.
 *
 * Archivo en ASCII.
 */
class recurso_asignaciones implements modelable
{
	static function _get_modelos()
	{
		return array();
	}

	static function get_list()
	{
		$ano = trim((string) ($_GET['ano_academico'] ?? ''));
		if (! preg_match('/^[0-9]{4}$/', $ano)) {
			throw new rest_error(400, 'Falta ano_academico (AAAA).');
		}
		$where = array(
			'trim(p.ano_academico) = ' . quote($ano),
			"(p.observaciones IS NULL OR p.observaciones NOT ILIKE '%[ocultar]%')",
		);
		if (isset($_GET['dni'])) {
			$dni = preg_replace('/[^0-9]/', '', (string) $_GET['dni']);
			if ($dni === '') {
				throw new rest_error(400, 'El dni tiene que ser numerico.');
			}
			$where[] = 'coalesce(p.dni_resp_vig, p.dni_resp) = ' . quote($dni);
		}
		$sql = 'SELECT p.id_programa, p.id_materia_prog AS id_materia, trim(p.ano_academico) AS ano_academico,
				p.periodo_dictado, p.dni_resp, p.legajo_resp, p.estado,
				coalesce(p.dni_resp_vig, p.dni_resp) AS dni_vigente, coalesce(p.legajo_resp_vig, p.legajo_resp) AS legajo_vigente
			FROM programas p
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY p.id_programa';
		$filas = toba::db()->consultar($sql);
		foreach ($filas as &$f) {
			foreach (array('id_programa', 'id_materia', 'dni_resp', 'legajo_resp', 'dni_vigente', 'legajo_vigente') as $c) {
				$f[$c] = ($f[$c] === null) ? null : (int) $f[$c];
			}
		}
		unset($f);
		rest::response()->get($filas);
	}
}
