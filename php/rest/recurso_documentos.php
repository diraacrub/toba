<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;
use SIUToba\rest\lib\rest_error;

require_once __DIR__ . '/../modelos/modelo_programa.php';
require_once __DIR__ . '/../informes/materias_equipo.php';

/**
 * @description Programas, planificaciones e informes de un anio, con su estado
 *
 * GET /rest/documentos?ano_academico=2026
 *     [&tipo=programa,planificacion,informe]
 *     [&estado=docente,depto,sac,aprobado,no_entregado]
 *     [&dni=12345678] [&legajo=12345] [&depto_principal=MATEMATICA]
 *
 * Una fila por documento: el programa, su planificacion y su informe pasan
 * cada uno por docente -> depto -> sac -> aprobado (o no_entregado). Sirve
 * para que Gestion Academica le muestre a cada uno lo que espera de el: al
 * docente lo suyo sin presentar, al departamento lo que tiene que avalar.
 * Solo lo necesario para eso, sin el contenido de los documentos.
 *
 * Las filas de programa traen ademas el equipo de catedra, para la ficha de
 * la catedra en Gestion Academica: equipo_catedra (texto, una persona por
 * linea en los del formulario integrado) y equipo_catedra_lista
 * ([{apellido, nombre, legajo, cargo, dedicacion}], o null si es texto
 * libre). El texto, como en /rest/programas
 * (modelo_programa::normalizar_programa); la lista trae ademas el legajo.
 * Todas traen cargo_resp.
 *
 * Desde 2027 (materias_equipo::DESDE) traen ademas:
 *   - publicado: si el docente lo ve (el programa vale tambien para su
 *     planificacion). Lo de antes siempre esta publicado.
 *   - el responsable vigente del programa (dni_vigente, legajo_vigente,
 *     apellido_vigente, nombre_vigente, cargo_vigente): el que dice Mocovi
 *     hoy, aunque el documento este firmado con otro. Igual al del programa
 *     si no cambio.
 *   - en la fila del programa, equipo_vigente: [{apellido, nombre, legajo,
 *     cargo, dedicacion}], los activos de huayca.materias_equipo para ese
 *     dictado (null antes de 2027 o si la materia no esta en esa tabla).
 *
 * El anio es el del programa (el de la planificacion casi siempre coincide;
 * el informe no tiene). Los programas ocultos (observaciones con "[ocultar]",
 * como en dt_programas) no cuentan, ni sus planificaciones e informes.
 * depto_principal se compara sin acentos, mayusculas ni espacios, como el
 * aval de los departamentos (en PHP: la base es LATIN1). Las reglas de cada pantalla (por ejemplo, que el aval de
 * programas deja afuera las carreras que empiezan con I) las aplica quien
 * consume: aca va cod_carrera para eso.
 *
 * Lo usa Gestion Academica (deptos-crub, catedras.py). Archivo en ASCII.
 */
class recurso_documentos implements modelable
{
	const TIPOS = array('programa', 'planificacion', 'informe');
	const ESTADOS = array('docente', 'depto', 'sac', 'aprobado', 'no_entregado');

	static function _get_modelos()
	{
		return array();
	}

	// "a,b" -> array de los valores permitidos; null si no vino el parametro.
	static function lista($nombre, $permitidos)
	{
		if (! isset($_GET[$nombre]) || trim((string) $_GET[$nombre]) === '') {
			return null;
		}
		$valores = array_unique(array_map('trim', explode(',', strtolower((string) $_GET[$nombre]))));
		$malos = array_diff($valores, $permitidos);
		if ($malos) {
			throw new rest_error(400, $nombre . ' desconocido: ' . implode(', ', $malos)
				. ' (se acepta ' . implode(', ', $permitidos) . ').');
		}
		return array_values($valores);
	}

	static function numero($nombre)
	{
		if (! isset($_GET[$nombre])) {
			return null;
		}
		$v = preg_replace('/[^0-9]/', '', (string) $_GET[$nombre]);
		if ($v === '') {
			throw new rest_error(400, 'El ' . $nombre . ' tiene que ser numerico.');
		}
		return $v;
	}

	static function utf8($v)
	{
		if ($v === null) {
			return null;
		}
		$v = trim((string) $v);
		return mb_check_encoding($v, 'UTF-8') ? $v : mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
	}

	// "Biologia General" == "BIOLOGIA GENERAL" (con tilde): mayusculas, sin acentos ni espacios.
	static function plano($v)
	{
		$v = mb_strtoupper(self::utf8($v), 'UTF-8');
		$v = strtr($v, array("\u{00C1}" => 'A', "\u{00C9}" => 'E', "\u{00CD}" => 'I', "\u{00D3}" => 'O',
			"\u{00DA}" => 'U', "\u{00DC}" => 'U', "\u{00D1}" => 'N'));
		return preg_replace('/[^A-Z0-9]/', '', $v);
	}

	static function get_list()
	{
		$ano = trim((string) ($_GET['ano_academico'] ?? ''));
		if (! preg_match('/^[0-9]{4}$/', $ano)) {
			throw new rest_error(400, 'Falta ano_academico (AAAA).');
		}
		$tipos = self::lista('tipo', self::TIPOS);
		$estados = self::lista('estado', self::ESTADOS);
		$dni = self::numero('dni');
		$legajo = self::numero('legajo');

		$where = array(
			'trim(p.ano_academico) = ' . quote($ano),
			"(p.observaciones IS NULL OR p.observaciones NOT ILIKE '%[ocultar]%')",
		);
		if ($dni !== null) {
			$where[] = 'p.dni_resp = ' . quote($dni);
		}
		if ($legajo !== null) {
			$where[] = 'p.legajo_resp = ' . quote($legajo);
		}
		$depto = trim((string) ($_GET['depto_principal'] ?? ''));

		// Lo comun de las tres: el programa y su materia.
		$comun = 'p.id_programa, p.id_materia_prog AS id_materia, trim(p.ano_academico) AS ano_academico,
			p.periodo_dictado, p.legajo_resp, p.dni_resp, p.apellido_resp, p.nombre_resp,
			p.cargo_resp, p.equipo_catedra,
			m.nombre_materia, m.cod_carrera, m.depto_principal, m.cod_guarani, m.plan_mocovi,
			coalesce(v.legajo_resp_vig, v.legajo_resp) AS legajo_vigente,
			coalesce(v.dni_resp_vig, v.dni_resp) AS dni_vigente,
			CASE WHEN v.legajo_resp_vig IS NULL THEN v.apellido_resp ELSE v.apellido_resp_vig END AS apellido_vigente,
			CASE WHEN v.legajo_resp_vig IS NULL THEN v.nombre_resp ELSE v.nombre_resp_vig END AS nombre_vigente,
			CASE WHEN v.legajo_resp_vig IS NULL THEN v.cargo_resp ELSE v.cargo_resp_vig END AS cargo_vigente';
		// v: el programa mismo (en el informe, p es la vista con el responsable del informe).
		$vig = 'JOIN programas v ON v.id_programa = p.id_programa';
		$desde = 'FROM programas p JOIN materias m ON m.id_materia = p.id_materia_prog';
		$filtro = implode(' AND ', $where);
		$partes = array(
			'programa' => "SELECT 'programa' AS tipo, p.id_programa AS id, p.estado, v.publicado, $comun
				$desde $vig WHERE $filtro",
			'planificacion' => "SELECT 'planificacion' AS tipo, pl.id_planificacion AS id, pl.estado_planificacion AS estado,
				v.publicado, $comun
				FROM planificaciones pl JOIN programas p ON p.id_programa = pl.id_prog_planif
				JOIN materias m ON m.id_materia = p.id_materia_prog $vig WHERE $filtro",
			'informe' => "SELECT 'informe' AS tipo, i.id_informe AS id, i.estado_informe AS estado, i.publicado, $comun
				FROM informes i JOIN informes_programas p ON p.id_informe_programa = i.id_informe
				JOIN materias m ON m.id_materia = p.id_materia_prog $vig WHERE $filtro",
		);
		$sql = array();
		foreach ($partes as $tipo => $s) {
			if ($tipos === null || in_array($tipo, $tipos)) {
				$sql[] = $s;
			}
		}
		$sql = 'SELECT * FROM (' . implode(' UNION ALL ', $sql) . ') d';
		if ($estados !== null) {
			$sql .= ' WHERE d.estado IN (' . implode(', ', array_map('quote', $estados)) . ')';
		}
		$sql .= ' ORDER BY d.id_programa, d.tipo';

		$salida = array();
		// El equipo vigente sale de materias_equipo (una consulta para el anio).
		$equipos = ((int) $ano >= materias_equipo::DESDE) ? materias_equipo::del_anio($ano) : array();
		foreach (toba::db()->consultar($sql) as $f) {
			foreach (array('id', 'id_programa', 'id_materia', 'legajo_resp', 'dni_resp', 'legajo_vigente', 'dni_vigente') as $c) {
				$f[$c] = ($f[$c] === null || $f[$c] === '') ? null : (int) $f[$c];
			}
			$f['publicado'] = in_array($f['publicado'], array(true, 't', '1', 1), true);
			$f['equipo_vigente'] = null;
			$k = materias_equipo::clave_materia($f['cod_guarani'], $f['cod_carrera'], $f['plan_mocovi']);
			if ($f['tipo'] === 'programa' && isset($equipos[$k])) {
				$f['equipo_vigente'] = array();
				foreach (materias_equipo::vigentes($equipos[$k], $f['periodo_dictado']) as $e) {
					$f['equipo_vigente'][] = array('apellido' => self::utf8($e[0]), 'nombre' => self::utf8($e[1]),
						'legajo' => $e[2], 'cargo' => $e[3], 'dedicacion' => $e[4]);
				}
			}
			unset($f['cod_guarani'], $f['plan_mocovi']);
			// El equipo, solo en la fila del programa (normalizar trabaja en LATIN1).
			if ($f['tipo'] === 'programa') {
				$f['equipo_catedra_crudo'] = $f['equipo_catedra'];
				$eq = modelo_programa::normalizar_programa(array('equipo_catedra' => $f['equipo_catedra']));
				$f['equipo_catedra'] = self::utf8($eq['equipo_catedra']);
				// La lista, con el legajo de cada uno (a diferencia de /rest/programas):
				// con el Gestion Academica le muestra la catedra a quien integra el equipo.
				$f['equipo_catedra_lista'] = null;
				$crudas = modelo_programa::equipo_desde_json($f['equipo_catedra_crudo']);
				if (is_array($crudas)) {
					$f['equipo_catedra_lista'] = array();
					foreach ($crudas as $fila) {
						if (! is_array($fila)) {
							continue;
						}
						$persona = array(
							'apellido' => trim((string) ($fila[0] ?? '')),
							'nombre' => trim((string) ($fila[1] ?? '')),
							'legajo' => preg_replace('/[^0-9]/', '', (string) ($fila[2] ?? '')),
							'cargo' => trim((string) ($fila[3] ?? '')),
							'dedicacion' => trim((string) ($fila[4] ?? '')),
						);
						if ($persona['apellido'] === '' && $persona['nombre'] === '') {
							continue;
						}
						$f['equipo_catedra_lista'][] = $persona;	// ya en UTF-8: salen del JSON
					}
				}
				unset($f['equipo_catedra_crudo']);
			} else {
				unset($f['equipo_catedra']);
			}
			// La base es LATIN1: los textos salen en UTF-8, como en /rest/materias.
			foreach (array('cargo_resp', 'apellido_resp', 'nombre_resp', 'nombre_materia', 'cod_carrera', 'depto_principal', 'periodo_dictado', 'estado',
				'apellido_vigente', 'nombre_vigente', 'cargo_vigente') as $c) {
				$f[$c] = self::utf8($f[$c]);
			}
			// El departamento se compara aca, sin acentos ni mayusculas ni
			// espacios: en SQL dependeria de la codificacion de la conexion.
			if ($depto !== '' && self::plano($f['depto_principal']) !== self::plano($depto)) {
				continue;
			}
			$salida[] = $f;
		}
		rest::response()->get($salida);
	}
}
