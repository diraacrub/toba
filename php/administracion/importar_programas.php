<?php
/**
 * Administracion > Importar programas (CSV)
 *
 * Da de alta los programas de un anio, cada uno con su planificacion y su
 * informe de catedra, a
 * partir de lo que arma deptos (/deptos/programas: el informe de Mocovi del
 * anio, con los dobles ya resueltos). deptos lo manda directo por la API
 * (POST /rest/alta_programas, que usa planificar y aplicar de esta clase);
 * esta pantalla queda para cargar el CSV a mano.
 *
 *   1. Subir: el CSV de deptos (ano_academico, id_materia, periodo_dictado,
 *      legajo_resp, id_designacion, cargo_resp, apellido_resp, nombre_resp,
 *      equipo_catedra, id_programa_anterior, materia).
 *   2. Vista previa, sin guardar nada: a crear (y si copian contenido), ya
 *      existen (y si les falta la planificacion o el informe) y con problemas.
 *   3. Confirmar: se vuelve a calcular contra la base y se crea todo en una
 *      sola transaccion.
 *
 * Reglas:
 *   - El DNI del responsable sale del usuario de Toba con ese legajo (ciu). Sin
 *     usuario o sin DNI, la fila no se carga: primero Importar usuarios.
 *   - Si viene id_programa_anterior, el programa nuevo arranca con su contenido
 *     (fundamentacion, objetivos, programa, bibliografia, metodologia,
 *     evaluacion, distribucion horaria y horas), sin el cronograma, que es de
 *     cada anio. Estado: docente.
 *   - Cada programa nuevo lleva su planificacion y su informe: uno de cada uno
 *     por programa, en estado docente (la planificacion, del mismo anio; el
 *     informe no tiene anio, es el del programa). La planificacion arranca con
 *     las publicaciones de la planificacion del programa anterior
 *     (self::$CONTENIDO_PLANIF: libros, apuntes, guias y publicaciones
 *     periodicas); lo demas es de cada anio y arranca vacio. El informe, vacio. La operacion Programas y Planificaciones necesita la
 *     planificacion para abrir el programa.
 *   - Un programa de ese anio, materia y periodo que ya existe no se toca; si
 *     le falta la planificacion o el informe, se le crea.
 *   - Cada alta queda en movimientos.
 *   - Los ids nuevos siguen al mas alto (como 2025 y 2026). La secuencia de
 *     programas no se toca (los ABM no la usan). Las de planificaciones e
 *     informes, que si usan los ABM, se adelantan hasta el mas alto (nunca se
 *     atrasan): si no, el proximo alta a mano chocaria con un numero usado.
 *   - Solo para el perfil admin.
 *
 * Archivo en ASCII: los acentos de la interfaz van como entidades HTML.
 */

class catedras_importar_programas
{
	const MAX_BYTES = 2097152;
	const MEMORIA = 'importar_programas_filas';

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
		return toba::db();
	}

	// ------------------------------------------------------------------
	// Lectura del CSV
	// ------------------------------------------------------------------

	static function leer_csv($ruta)
	{
		$texto = catedras_importar_usuarios_texto::a_latin1(file_get_contents($ruta));
		$f = fopen('php://memory', 'r+');
		fwrite($f, $texto);
		rewind($f);
		$primera = fgets($f);
		$sep = (substr_count($primera, ';') > substr_count($primera, ',')) ? ';' : ',';
		rewind($f);
		$cab = array();
		foreach (fgetcsv($f, 0, $sep) as $i => $c) {
			$cab[strtolower(trim($c))] = $i;
		}
		foreach (array('ano_academico', 'id_materia', 'periodo_dictado', 'legajo_resp') as $obligatoria) {
			if (!isset($cab[$obligatoria])) {
				fclose($f);
				return array(array(), 'El archivo no es el de deptos: falta la columna <strong>' . $obligatoria . '</strong>.');
			}
		}
		$filas = array();
		$n = 1;
		while (($c = fgetcsv($f, 0, $sep)) !== false) {
			$n++;
			if (count($c) === 1 && trim((string) $c[0]) === '') {
				continue;
			}
			$v = function ($campo) use ($c, $cab) {
				return (isset($cab[$campo]) && isset($c[$cab[$campo]])) ? trim($c[$cab[$campo]]) : '';
			};
			$filas[] = array(
				'linea' => $n,
				'ano' => $v('ano_academico'),
				'id_materia' => $v('id_materia'),
				'periodo' => $v('periodo_dictado'),
				'legajo' => $v('legajo_resp'),
				'id_designacion' => $v('id_designacion'),
				'cargo' => $v('cargo_resp'),
				'apellido' => $v('apellido_resp'),
				'nombre' => $v('nombre_resp'),
				'equipo' => $v('equipo_catedra'),
				'anterior' => $v('id_programa_anterior'),
				'materia' => $v('materia'),
			);
		}
		fclose($f);
		if (!$filas) {
			return array(array(), 'El archivo no tiene filas de datos.');
		}
		return array($filas, null);
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
		$con_informe = array();
		foreach ($db->consultar('SELECT DISTINCT id_prog_informe FROM informes') as $in) {
			$con_informe[(int) $in['id_prog_informe']] = true;
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
				if (!isset($con_informe[$id])) {
					$falta[] = 'informe';
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

	/** El informe de catedra vacio de un programa, en estado docente. */
	static function crear_informe($db, $id, $id_programa, $usuario, $obs)
	{
		$db->ejecutar('INSERT INTO informes (id_informe, id_prog_informe, estado_informe) VALUES ('
			. (int) $id . ', ' . (int) $id_programa . ", 'docente')");
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
	 * Crea lo planificado. Devuelve lo creado: array de array(linea,
	 * id_programa, id_planificacion, id_informe, nuevo_programa); en un
	 * programa que ya estaba, id_planificacion o id_informe es null si ya lo tenia.
	 * $usuario es quien queda en movimientos (por omision, el de la sesion).
	 */
	static function aplicar($plan, $usuario = null, $origen = 'deptos')
	{
		$db = self::db();
		$usuario = $usuario !== null ? $usuario : toba::usuario()->get_id();
		$creados = array();
		$copiar = implode(', ', self::$CONTENIDO);
		$db->abrir_transaccion();
		try {
			// Los ids nuevos los pone el importador: el siguiente al mas alto,
			// como se cargaron 2025 y 2026. Las tablas quedan bloqueadas hasta
			// el final, para que nadie tome el mismo numero en el medio.
			$db->ejecutar('LOCK TABLE programas, planificaciones, informes IN SHARE ROW EXCLUSIVE MODE');
			$max = $db->consultar_fila('SELECT coalesce(max(id_programa), 0) AS n FROM programas');
			$siguiente = (int) $max['n'];
			$max = $db->consultar_fila('SELECT coalesce(max(id_planificacion), 0) AS n FROM planificaciones');
			$siguiente_planif = (int) $max['n'];
			$max = $db->consultar_fila('SELECT coalesce(max(id_informe), 0) AS n FROM informes');
			$siguiente_informe = (int) $max['n'];
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
				$cols = implode(', ', array_keys($valores));
				$vals = implode(', ', array_map(function ($v) use ($db) {
					return $v === null ? 'NULL' : (is_int($v) ? (string) $v : $db->quote($v));
				}, array_values($valores)));
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
				$siguiente_informe++;
				self::crear_informe($db, $siguiente_informe, (int) $r['id_programa'], $usuario,
					'Importado desde ' . $origen . ' con el programa ' . (int) $r['id_programa']);
				$creados[] = array('linea' => $f['linea'], 'id_programa' => (int) $r['id_programa'],
					'id_planificacion' => $siguiente_planif, 'id_informe' => $siguiente_informe, 'nuevo_programa' => true);
			}
			foreach ($plan['incompletos'] as $p) {
				$c = array('linea' => $p['fila']['linea'], 'id_programa' => (int) $p['id'],
					'id_planificacion' => null, 'id_informe' => null, 'nuevo_programa' => false);
				if (in_array('planificacion', $p['falta'])) {
					$c['id_planificacion'] = ++$siguiente_planif;
					self::crear_planificacion($db, $siguiente_planif, (int) $p['id'], (int) $p['fila']['ano'], $usuario,
						'Importado desde ' . $origen . ': el programa ' . (int) $p['id'] . ' no tenia planificacion', $p['fila']['anterior']);
				}
				if (in_array('informe', $p['falta'])) {
					$c['id_informe'] = ++$siguiente_informe;
					self::crear_informe($db, $siguiente_informe, (int) $p['id'], $usuario,
						'Importado desde ' . $origen . ': el programa ' . (int) $p['id'] . ' no tenia informe');
				}
				$creados[] = $c;
			}
			self::adelantar_secuencia($db, 'planificaciones_id_planificacion_seq', 'planificaciones', 'id_planificacion');
			self::adelantar_secuencia($db, 'informes_id_informe_seq', 'informes', 'id_informe');
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('IMPORTAR PROGRAMAS: ' . $e->getMessage());
			throw $e;
		}
		toba::logger()->notice('IMPORTAR PROGRAMAS por ' . $usuario . ': ' . count($plan['nuevos']) . ' nuevos con planificacion e informe, '
			. count($plan['incompletos']) . ' programas que ya estaban completados');
		return $creados;
	}

	// ------------------------------------------------------------------
	// Pantalla
	// ------------------------------------------------------------------

	static function url()
	{
		return toba::vinculador()->get_url();
	}

	static function campo_csrf()
	{
		echo toba_form::hidden(apex_sesion_csrt, toba::memoria()->fijar_csrf_token(true));
	}

	static function aviso($texto, $tipo = 'error')
	{
		echo '<div class="hy-import-aviso hy-import-aviso-' . $tipo . '">' . $texto . '</div>';
	}

	static function form_subir()
	{
		echo '<section class="hy-tarjeta">';
		echo '<h2>1. Elegir el archivo</h2>';
		echo '<p>El CSV que se descarga de <strong>deptos &rarr; Programas</strong>: los programas del a&ntilde;o armados desde el informe de Mocovi, con los casos de dos responsables ya resueltos.</p>';
		echo '<form method="post" enctype="multipart/form-data" action="' . self::h(self::url()) . '" class="hy-import-form">';
		self::campo_csrf();
		echo '<input type="hidden" name="paso" value="previa">';
		echo '<input type="file" name="csv" accept=".csv,.txt,text/csv" required>';
		echo '<button type="submit" class="hy-boton-primario">Ver qu&eacute; va a pasar</button>';
		echo '</form>';
		echo '<p class="hy-nota">Cada programa nuevo arranca en estado <strong>docente</strong> con el contenido del programa del a&ntilde;o anterior (menos el cronograma), si lo hay, y con su planificaci&oacute;n (con las publicaciones de la del a&ntilde;o anterior) y su informe vac&iacute;o. El DNI del responsable sale de su usuario. Un programa que ya existe no se toca; si le falta la planificaci&oacute;n o el informe, se le crea.</p>';
		echo '</section>';
	}

	static function tabla($titulo, $lista, $con_motivo = false)
	{
		if (!$lista) {
			return;
		}
		echo '<h3>' . $titulo . ' (' . count($lista) . ')</h3>';
		echo '<div class="hy-import-tabla"><table><thead><tr><th>Fila</th><th>Materia</th><th>Carrera</th><th>Per&iacute;odo</th><th>Responsable</th><th>' . ($con_motivo ? 'Motivo' : 'Contenido') . '</th></tr></thead><tbody>';
		foreach ($lista as $p) {
			$f = $p['fila'];
			echo '<tr><td>' . (int) $f['linea'] . '</td><td>' . self::h($f['materia'] ?: $f['id_materia']) . '</td><td>' . self::h($f['carrera'])
				. '</td><td>' . self::h($f['periodo']) . '</td><td>' . self::h($f['apellido'] . ', ' . $f['nombre']) . ' <span class="hy-nota">(' . self::h($f['legajo']) . ')</span></td><td>';
			if ($con_motivo) {
				echo implode('; ', $p['motivos']);
			} elseif (!empty($p['falta'])) {
				echo 'Programa ' . (int) $p['id'] . ': se crea ' . str_replace('planificacion', 'la planificaci&oacute;n',
					str_replace('informe', 'el informe', implode(' y ', $p['falta'])));
			} elseif (isset($p['id'])) {
				echo 'Ya existe (programa ' . (int) $p['id'] . ')';
			} else {
				echo ($f['anterior'] !== '' ? 'Copia el programa ' . (int) $f['anterior'] : 'Vac&iacute;o') . ' &middot; con planificaci&oacute;n e informe';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	static function previa($plan)
	{
		echo '<section class="hy-tarjeta">';
		echo '<h2>2. Revisar</h2>';
		echo '<p><strong>' . count($plan['nuevos']) . '</strong> programas a crear, cada uno con su planificaci&oacute;n y su informe &middot; '
			. count($plan['existen']) . ' ya existen &middot; '
			. ($plan['incompletos'] ? count($plan['incompletos']) . ' ya existen sin planificaci&oacute;n o sin informe &middot; ' : '')
			. count($plan['problemas']) . ' con problemas.</p>';
		self::tabla('Con problemas: no se cargan', $plan['problemas'], true);
		self::tabla('A crear: programa, planificaci&oacute;n e informe', $plan['nuevos']);
		self::tabla('Ya existen: se crea lo que les falta', $plan['incompletos']);
		self::tabla('Ya existen: no se tocan', $plan['existen']);
		echo '<div class="hy-import-acciones">';
		if ($plan['nuevos'] || $plan['incompletos']) {
			echo '<form method="post" action="' . self::h(self::url()) . '">';
			self::campo_csrf();
			echo '<input type="hidden" name="paso" value="confirmar">';
			echo '<button type="submit" class="hy-boton-primario">Confirmar: crear ' . count($plan['nuevos']) . ' programas'
				. ' con su planificaci&oacute;n e informe' . ($plan['incompletos'] ? ' y completar ' . count($plan['incompletos']) . ' que ya estaban' : '') . '</button>';
			echo '</form>';
		} else {
			echo '<p>No hay nada para crear.</p>';
		}
		echo '<a class="hy-boton-secundario" href="' . self::h(self::url()) . '">Elegir otro archivo</a>';
		echo '</div>';
		echo '</section>';
	}

	static function ejecutar()
	{
		$perfiles = array_map('strtolower', toba::usuario()->get_perfiles_funcionales());
		echo '<div class="hy-inicio hy-import">';
		echo '<header class="hy-inicio-saludo"><p class="hy-inicio-bajada">Dar de alta los programas de un a&ntilde;o, con sus planificaciones e informes, desde el CSV de deptos. Primero se ve qu&eacute; va a pasar; no se guarda nada hasta confirmar.</p></header>';
		if (!in_array('admin', $perfiles)) {
			self::aviso('Esta secci&oacute;n es solo para administradores.');
			echo '</div>';
			return;
		}
		$paso = isset($_POST['paso']) ? $_POST['paso'] : '';
		if ($paso === 'previa') {
			$archivo = isset($_FILES['csv']) ? $_FILES['csv'] : null;
			if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
				self::aviso('No se recibi&oacute; el archivo. Prob&aacute; de nuevo.');
			} elseif ($archivo['size'] > self::MAX_BYTES) {
				self::aviso('El archivo es demasiado grande (m&aacute;ximo 2 MB).');
			} else {
				list($filas, $error) = self::leer_csv($archivo['tmp_name']);
				if ($error) {
					self::aviso($error);
				} else {
					toba::memoria()->set_dato_operacion(self::MEMORIA, $filas);
					self::previa(self::planificar($filas));
					echo '</div>';
					return;
				}
			}
		} elseif ($paso === 'confirmar') {
			$filas = toba::memoria()->get_dato_operacion(self::MEMORIA);
			if (!is_array($filas)) {
				self::aviso('Se perdi&oacute; la vista previa. Volv&eacute; a subir el archivo.');
			} else {
				$plan = self::planificar($filas);
				try {
					self::aplicar($plan, null, 'el CSV de deptos');
					toba::memoria()->eliminar_dato_operacion(self::MEMORIA);
					self::aviso('Listo: se crearon <strong>' . count($plan['nuevos']) . '</strong> programas, cada uno con su planificaci&oacute;n y su informe.'
						. ($plan['incompletos'] ? ' Y se complet&oacute; lo que les faltaba a ' . count($plan['incompletos']) . ' programas que ya estaban.' : '')
						. ($plan['problemas'] ? ' ' . count($plan['problemas']) . ' filas con problemas no se cargaron.' : ''), 'ok');
				} catch (Exception $e) {
					self::aviso('No se pudo guardar y no se cre&oacute; nada. Detalle en el log de Toba.');
				}
			}
		}
		self::form_subir();
		echo '</div>';
	}
}

/** La conversion a LATIN1 de Importar usuarios, sin ejecutar esa pantalla. */
class catedras_importar_usuarios_texto
{
	static function a_latin1($bytes)
	{
		if (substr($bytes, 0, 3) === "\xEF\xBB\xBF") {
			$bytes = substr($bytes, 3);
		}
		if (preg_match('/[\x80-\xFF]/', $bytes) && mb_check_encoding($bytes, 'UTF-8')) {
			return mb_convert_encoding($bytes, 'ISO-8859-1', 'UTF-8');
		}
		if (preg_match('/[\x80-\x9F]/', $bytes)) {
			return mb_convert_encoding(mb_convert_encoding($bytes, 'UTF-8', 'Windows-1252'), 'ISO-8859-1', 'UTF-8');
		}
		return $bytes;
	}
}

if (!defined('CATEDRAS_IMPORTAR_PROGRAMAS_SIN_EJECUTAR')) {
	catedras_importar_programas::ejecutar();
}
