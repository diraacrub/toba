<?php
/**
 * Administracion > Importar programas (CSV)
 *
 * Da de alta los programas de un anio a partir del CSV que arma deptos
 * (/deptos/programas: la planilla de cargos, con los dobles ya resueltos).
 *
 *   1. Subir: el CSV de deptos (ano_academico, id_materia, periodo_dictado,
 *      legajo_resp, id_designacion, cargo_resp, apellido_resp, nombre_resp,
 *      equipo_catedra, id_programa_anterior, materia).
 *   2. Vista previa, sin guardar nada: a crear (y si copian contenido), ya
 *      existen y con problemas.
 *   3. Confirmar: se vuelve a calcular contra la base y se crea todo en una
 *      sola transaccion.
 *
 * Reglas:
 *   - El DNI del responsable sale del usuario de Toba con ese legajo (ciu). Sin
 *     usuario o sin DNI, la fila no se carga: primero Importar usuarios.
 *   - Si viene id_programa_anterior, el programa nuevo arranca con su contenido
 *     (fundamentacion, objetivos, programa, bibliografia, metodologia,
 *     evaluacion, distribucion horaria, cronograma y horas). Estado: docente.
 *   - Un programa de ese anio, materia y periodo que ya existe no se toca.
 *   - Cada alta queda en movimientos.
 *   - Los id_programa nuevos siguen al mas alto (como 2025 y 2026). Los que
 *     existen no se tocan, y tampoco la secuencia de la tabla.
 *   - Solo para el perfil admin.
 *
 * Archivo en ASCII: los acentos de la interfaz van como entidades HTML.
 */

class catedras_importar_programas
{
	const MAX_BYTES = 2097152;
	const MEMORIA = 'importar_programas_filas';

	// Lo que se copia del programa del anio anterior.
	static $CONTENIDO = array('fundamentacion', 'objetivos', 'programa_analitico', 'bibliografia',
		'propuesta_metodologica', 'evaluacion_acreditacion', 'distribucion_horaria', 'cronograma_tentativo',
		'horas_teoricas', 'horas_practicas', 'horas_teoricopracticas');

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
		$plan = array('nuevos' => array(), 'existen' => array(), 'problemas' => array());

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
				$plan['existen'][] = array('fila' => $f, 'id' => $programas[$clave]);
			} else {
				$plan['nuevos'][] = array('fila' => $f);
			}
		}
		return $plan;
	}

	// ------------------------------------------------------------------
	// Aplicar (una sola transaccion)
	// ------------------------------------------------------------------

	static function aplicar($plan)
	{
		$db = self::db();
		$usuario = toba::usuario()->get_id();
		$copiar = implode(', ', self::$CONTENIDO);
		$db->abrir_transaccion();
		try {
			// El id de cada programa nuevo lo pone el importador: el siguiente al
			// mas alto, como se cargaron 2025 y 2026. No se usa ni se toca la
			// secuencia de la tabla (esta atrasada). La tabla queda bloqueada
			// hasta el final, para que nadie tome el mismo numero en el medio.
			$db->ejecutar('LOCK TABLE programas IN SHARE ROW EXCLUSIVE MODE');
			$max = $db->consultar_fila('SELECT coalesce(max(id_programa), 0) AS n FROM programas');
			$siguiente = (int) $max['n'];
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
					. $db->quote('Importado desde la planilla' . ($f['anterior'] !== '' ? ', con el contenido del programa ' . (int) $f['anterior'] : ''))
					. ", 'docente')");
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('IMPORTAR PROGRAMAS: ' . $e->getMessage());
			throw $e;
		}
		toba::logger()->notice('IMPORTAR PROGRAMAS por ' . $usuario . ': ' . count($plan['nuevos']) . ' nuevos');
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
		echo '<p>El CSV que se descarga de <strong>deptos &rarr; Programas</strong>: los programas del a&ntilde;o armados desde la planilla de cargos, con los casos de dos responsables ya resueltos.</p>';
		echo '<form method="post" enctype="multipart/form-data" action="' . self::h(self::url()) . '" class="hy-import-form">';
		self::campo_csrf();
		echo '<input type="hidden" name="paso" value="previa">';
		echo '<input type="file" name="csv" accept=".csv,.txt,text/csv" required>';
		echo '<button type="submit" class="hy-boton-primario">Ver qu&eacute; va a pasar</button>';
		echo '</form>';
		echo '<p class="hy-nota">Los programas nuevos arrancan en estado <strong>docente</strong> con el contenido del programa del a&ntilde;o anterior, si lo hay. El DNI del responsable sale de su usuario. Un programa que ya existe no se toca.</p>';
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
			} elseif (isset($p['id'])) {
				echo 'Ya existe (programa ' . (int) $p['id'] . ')';
			} else {
				echo $f['anterior'] !== '' ? 'Copia el programa ' . (int) $f['anterior'] : 'Vac&iacute;o';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	static function previa($plan)
	{
		echo '<section class="hy-tarjeta">';
		echo '<h2>2. Revisar</h2>';
		echo '<p><strong>' . count($plan['nuevos']) . '</strong> a crear &middot; ' . count($plan['existen']) . ' ya existen &middot; '
			. count($plan['problemas']) . ' con problemas.</p>';
		self::tabla('Con problemas: no se cargan', $plan['problemas'], true);
		self::tabla('A crear', $plan['nuevos']);
		self::tabla('Ya existen: no se tocan', $plan['existen']);
		echo '<div class="hy-import-acciones">';
		if ($plan['nuevos']) {
			echo '<form method="post" action="' . self::h(self::url()) . '">';
			self::campo_csrf();
			echo '<input type="hidden" name="paso" value="confirmar">';
			echo '<button type="submit" class="hy-boton-primario">Confirmar: crear ' . count($plan['nuevos']) . ' programas</button>';
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
		echo '<header class="hy-inicio-saludo"><p class="hy-inicio-bajada">Dar de alta los programas de un a&ntilde;o desde el CSV de deptos. Primero se ve qu&eacute; va a pasar; no se guarda nada hasta confirmar.</p></header>';
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
					self::aplicar($plan);
					toba::memoria()->eliminar_dato_operacion(self::MEMORIA);
					self::aviso('Listo: se crearon <strong>' . count($plan['nuevos']) . '</strong> programas.'
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
