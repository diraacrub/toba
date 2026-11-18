<?php
/**
 * El alta de los programas de un anio, cada uno con su planificacion y su
 * informe, desde el informe de Mocovi que Secretaria sube en deptos
 * (/deptos/programas). La usa catedras_mocovi (php/administracion/mocovi_anio.php,
 * POST /rest/mocovi-anio), que hace en una sola pasada el equipo, el alta y
 * la actualizacion de lo que ya existe. Antes habia una pantalla para cargar
 * un CSV a mano (Administracion > Importar programas): se saco el 2026-11-18
 * para que haya un solo camino.
 *
 * Cada fila: ano, id_materia, periodo, legajo, id_designacion, cargo,
 * apellido, nombre, equipo (JSON), anterior (id_programa), materia.
 *
 * Reglas:
 *   - El DNI del responsable sale del usuario de Toba con ese legajo (ciu). Sin
 *     usuario o sin DNI, la fila no se carga.
 *   - Si viene el programa anterior, el nuevo arranca con su contenido
 *     (fundamentacion, objetivos, programa, bibliografia, metodologia,
 *     evaluacion, distribucion horaria y horas), sin el cronograma, que es de
 *     cada anio. Estado: docente.
 *   - Cada programa nuevo lleva su planificacion (con las publicaciones de la
 *     del programa anterior, self::$CONTENIDO_PLANIF; lo demas arranca vacio)
 *     y su informe de catedra vacio. El programa y el informe nacen SIN
 *     PUBLICAR: el docente no los ve hasta que Secretaria los publica desde
 *     deptos. La operacion Programas y Planificaciones necesita la
 *     planificacion para abrir el programa.
 *   - Un programa de ese anio, materia y periodo que ya existe no se toca; si
 *     le falta la planificacion, se le crea.
 *   - Cada alta queda en movimientos.
 *   - Los ids nuevos siguen al mas alto (como 2025 y 2026). La secuencia de
 *     programas no se toca (los ABM no la usan). Las de planificaciones e
 *     informes, que si usan los ABM, se adelantan hasta el mas alto (nunca se
 *     atrasan): si no, el proximo alta a mano chocaria con un numero usado.
 *
 * Archivo en ASCII.
 */

class catedras_importar_programas
{
	// Lo que se copia del programa del anio anterior (el cronograma no: es de cada anio).
	static $CONTENIDO = array('fundamentacion', 'objetivos', 'programa_analitico', 'bibliografia',
		'propuesta_metodologica', 'evaluacion_acreditacion', 'distribucion_horaria',
		'horas_teoricas', 'horas_practicas', 'horas_teoricopracticas');

	// Lo que se copia de la planificacion del programa anterior: las
	// publicaciones de la catedra. Lo demas es de cada anio y arranca vacio:
	// distribucion horaria, horarios de consulta, otras tareas, bibliografia
	// pedida, actividades internas y externas, el equipo (la operacion conjunta
	// lo toma del programa), opiniones, observaciones, comentarios y firmas.
	static $CONTENIDO_PLANIF = array('libros_pub', 'apuntes_pub', 'guia_trabajos_pub', 'publicaciones_periodicas');

	static function h($texto)
	{
		return htmlspecialchars((string) $texto, ENT_QUOTES, 'ISO-8859-1');
	}

	static function db()
	{
		return toba::db('catedras');
	}

	// ------------------------------------------------------------------
	// Plan: que va a pasar con cada fila (siempre contra la base)
	// ------------------------------------------------------------------

	static function planificar($filas)
	{
		$db = self::db();
		$plan = array('nuevos' => array(), 'existen' => array(), 'incompletos' => array(), 'problemas' => array());

		$dni_de = array();
		foreach (toba::instancia()->get_db()->consultar('SELECT usuario, ciu FROM apex_usuario') as $u) {
			$dni_de[trim((string) $u['usuario'])] = preg_replace('/\D/', '', (string) $u['ciu']);
		}
		$materias = array();
		foreach ($db->consultar('SELECT id_materia, nombre_materia, cod_carrera FROM materias') as $m) {
			$materias[(int) $m['id_materia']] = $m;
		}
		$programas = array();
		$ids = array();
		foreach ($db->consultar('SELECT id_programa, id_materia_prog, periodo_dictado, trim(ano_academico) AS ano FROM programas') as $p) {
			$programas[$p['ano'] . '|' . (int) $p['id_materia_prog'] . '|' . trim($p['periodo_dictado'])] = $p['id_programa'];
			$ids[(int) $p['id_programa']] = true;
		}
		$con_planif = array();
		foreach ($db->consultar('SELECT DISTINCT id_prog_planif FROM planificaciones') as $pl) {
			$con_planif[(int) $pl['id_prog_planif']] = true;
		}

		$vistos = array();
		foreach ($filas as $f) {
			$prob = array();
			if (!preg_match('/^\d{4}$/', $f['ano'])) {
				$prob[] = 'el a&ntilde;o no es v&aacute;lido';
			}
			if (!ctype_digit($f['id_materia']) || !isset($materias[(int) $f['id_materia']])) {
				$prob[] = 'la materia ' . self::h($f['id_materia']) . ' no existe';
			}
			if ($f['periodo'] === '') {
				$prob[] = 'falta el per&iacute;odo';
			}
			$dni = isset($dni_de[$f['legajo']]) ? $dni_de[$f['legajo']] : null;
			if ($dni === null) {
				$prob[] = 'el legajo ' . self::h($f['legajo']) . ' no tiene usuario en C&aacute;tedras (import&aacute; los usuarios primero)';
			} elseif ($dni === '') {
				$prob[] = 'el usuario ' . self::h($f['legajo']) . ' no tiene DNI cargado';
			}
			if ($f['anterior'] !== '' && (!ctype_digit($f['anterior']) || !isset($ids[(int) $f['anterior']]))) {
				$prob[] = 'el programa anterior ' . self::h($f['anterior']) . ' no existe';
			}
			$clave = $f['ano'] . '|' . (int) $f['id_materia'] . '|' . $f['periodo'];
			if (isset($vistos[$clave])) {
				$prob[] = 'repetido en el archivo (fila ' . $vistos[$clave] . ')';
			}
			$vistos += array($clave => $f['linea']);
			$f['dni'] = $dni;
			$f['carrera'] = isset($materias[(int) $f['id_materia']]) ? $materias[(int) $f['id_materia']]['cod_carrera'] : '';

			if ($prob) {
				$plan['problemas'][] = array('fila' => $f, 'motivos' => $prob);
			} elseif (isset($programas[$clave])) {
				$id = (int) $programas[$clave];
				$falta = array();
				if (!isset($con_planif[$id])) {
					$falta[] = 'planificacion';
				}
				$existe = array('fila' => $f, 'id' => $id, 'falta' => $falta);
				if ($falta) {
					$plan['incompletos'][] = $existe;
				} else {
					$plan['existen'][] = $existe;
				}
			} else {
				$plan['nuevos'][] = array('fila' => $f);
			}
		}
		return $plan;
	}

	// ------------------------------------------------------------------
	// Aplicar (una sola transaccion)
	// ------------------------------------------------------------------

	/**
	 * La planificacion de un programa: del mismo anio, en estado docente, con
	 * lo que no es de cada anio de la planificacion del programa anterior (si
	 * lo hay y tiene planificacion; si tiene dos, la mas nueva). Devuelve el
	 * id de la planificacion copiada, o null si arranca vacia.
	 */
	static function crear_planificacion($db, $id, $id_programa, $ano, $usuario, $obs, $anterior = '')
	{
		$copia = null;
		if ($anterior !== '' && ctype_digit((string) $anterior)) {
			$r = $db->consultar_fila('SELECT max(id_planificacion) AS id FROM planificaciones WHERE id_prog_planif = ' . (int) $anterior);
			$copia = (isset($r['id']) && $r['id'] !== null) ? (int) $r['id'] : null;
		}
		$vals = (int) $id . ', ' . (int) $ano . ', ' . (int) $id_programa . ", 'docente'";
		if ($copia !== null) {
			$cols = implode(', ', self::$CONTENIDO_PLANIF);
			$db->ejecutar("INSERT INTO planificaciones (id_planificacion, ano_acad_planif, id_prog_planif, estado_planificacion, $cols) "
				. "SELECT $vals, $cols FROM planificaciones WHERE id_planificacion = $copia");
			$obs .= ', con lo de la planificacion ' . $copia;
		} else {
			$db->ejecutar("INSERT INTO planificaciones (id_planificacion, ano_acad_planif, id_prog_planif, estado_planificacion) VALUES ($vals)");
		}
		$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) VALUES ('planificaciones', "
			. (int) $id . ", 'Alta', " . $db->quote($usuario) . ', ' . $db->quote($obs) . ", 'docente')");
		return $copia;
	}

	/** El informe de catedra vacio de un programa, en estado docente y sin publicar. */
	static function crear_informe($db, $id, $id_programa, $usuario, $obs)
	{
		$db->ejecutar('INSERT INTO informes (id_informe, id_prog_informe, estado_informe, publicado) VALUES ('
			. (int) $id . ', ' . (int) $id_programa . ", 'docente', false)");
		$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) VALUES ('informes', "
			. (int) $id . ", 'Alta', " . $db->quote($usuario) . ', ' . $db->quote($obs) . ", 'docente')");
	}

	/** Adelanta la secuencia hasta el id mas alto de la tabla; nunca la atrasa. */
	static function adelantar_secuencia($db, $secuencia, $tabla, $columna)
	{
		$db->consultar_fila("SELECT setval('$secuencia', greatest((SELECT last_value FROM $secuencia),"
			. " (SELECT coalesce(max($columna), 1) FROM $tabla)))");
	}

	/**
	 * Crea lo planificado, dentro de una transaccion que ya abrio quien llama
	 * con programas, planificaciones e informes bloqueadas (catedras_mocovi).
	 * Cada programa nuevo, con su planificacion y su informe, sin publicar.
	 * Devuelve lo creado: array de array(linea, id_programa, id_planificacion,
	 * id_informe, nuevo_programa); en un programa que ya estaba,
	 * id_planificacion es null si ya la tenia.
	 */
	static function aplicar_en($db, $plan, $usuario, $origen = 'deptos')
	{
		$creados = array();
		$copiar = implode(', ', self::$CONTENIDO);
		$max = $db->consultar_fila('SELECT coalesce(max(id_programa), 0) AS n FROM programas');
		$siguiente = (int) $max['n'];
		$max = $db->consultar_fila('SELECT coalesce(max(id_planificacion), 0) AS n FROM planificaciones');
		$siguiente_planif = (int) $max['n'];
		$max = $db->consultar_fila('SELECT coalesce(max(id_informe), 0) AS n FROM informes');
		$siguiente_inf = (int) $max['n'];
		foreach ($plan['nuevos'] as $p) {
			$siguiente++;
			$f = $p['fila'];
			$valores = array(
				'id_programa' => $siguiente,
				'id_designacion' => $f['id_designacion'] !== '' && ctype_digit($f['id_designacion']) ? (int) $f['id_designacion'] : null,
				'legajo_resp' => (int) $f['legajo'],
				'dni_resp' => (int) $f['dni'],
				'apellido_resp' => $f['apellido'],
				'nombre_resp' => $f['nombre'],
				'cargo_resp' => $f['cargo'],
				'equipo_catedra' => $f['equipo'] !== '' ? $f['equipo'] : null,
				'id_materia_prog' => (int) $f['id_materia'],
				'periodo_dictado' => $f['periodo'],
				'ano_academico' => $f['ano'],
				'estado' => 'docente',
			);
			$cols = implode(', ', array_keys($valores)) . ', publicado';
			$vals = implode(', ', array_map(function ($v) use ($db) {
				return $v === null ? 'NULL' : (is_int($v) ? (string) $v : $db->quote($v));
			}, array_values($valores))) . ', false';
			if ($f['anterior'] !== '') {
				$sql = "INSERT INTO programas ($cols, $copiar) SELECT $vals, $copiar FROM programas WHERE id_programa = "
					. (int) $f['anterior'] . ' RETURNING id_programa';
			} else {
				$sql = "INSERT INTO programas ($cols) VALUES ($vals) RETURNING id_programa";
			}
			$r = $db->consultar_fila($sql);
			$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) VALUES ('programas', "
				. (int) $r['id_programa'] . ", 'Alta', " . $db->quote($usuario) . ', '
				. $db->quote('Importado desde ' . $origen . ' (informe de Mocovi)' . ($f['anterior'] !== '' ? ', con el contenido del programa ' . (int) $f['anterior'] : ''))
				. ", 'docente')");
			$siguiente_planif++;
			self::crear_planificacion($db, $siguiente_planif, (int) $r['id_programa'], (int) $f['ano'], $usuario,
				'Importado desde ' . $origen . ' con el programa ' . (int) $r['id_programa'], $f['anterior']);
			$siguiente_inf++;
			self::crear_informe($db, $siguiente_inf, (int) $r['id_programa'], $usuario,
				'Creado desde ' . $origen . ' con el programa ' . (int) $r['id_programa']);
			$creados[] = array('linea' => $f['linea'], 'id_programa' => (int) $r['id_programa'],
				'id_planificacion' => $siguiente_planif, 'id_informe' => $siguiente_inf, 'nuevo_programa' => true);
		}
		foreach ($plan['incompletos'] as $p) {
			$c = array('linea' => $p['fila']['linea'], 'id_programa' => (int) $p['id'],
				'id_planificacion' => null, 'id_informe' => null, 'nuevo_programa' => false);
			if (in_array('planificacion', $p['falta'])) {
				$c['id_planificacion'] = ++$siguiente_planif;
				self::crear_planificacion($db, $siguiente_planif, (int) $p['id'], (int) $p['fila']['ano'], $usuario,
					'Importado desde ' . $origen . ': el programa ' . (int) $p['id'] . ' no tenia planificacion', $p['fila']['anterior']);
			}
			$creados[] = $c;
		}
		return $creados;
	}

	/** Las secuencias de planificaciones e informes, hasta el id mas alto (al confirmar). */
	static function adelantar_secuencias($db)
	{
		self::adelantar_secuencia($db, 'planificaciones_id_planificacion_seq', 'planificaciones', 'id_planificacion');
		self::adelantar_secuencia($db, 'informes_id_informe_seq', 'informes', 'id_informe');
	}
}
