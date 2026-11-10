<?php
/**
 * Administracion > Importar usuarios (CSV)
 *
 * Reemplaza al comando de consola poblar_usuarios: el admin sube el CSV de
 * responsables, ve que va a pasar y recien entonces confirma.
 *
 *   1. Subir: CSV con columnas usuario (o legajo), dni, nombre, email. Acepta
 *      UTF-8, Latin-1 o Windows-1252 y separador "," o ";": todo se convierte
 *      a LATIN1, que es la codificacion de la base (asi no se rompen la N con
 *      tilde y los acentos, ver deploy/sql/arreglar_nombres_utf8.sql).
 *   2. Vista previa, sin guardar nada: nuevos, a actualizar (antes -> despues),
 *      sin cambios y con problemas.
 *   3. Confirmar: se vuelve a calcular contra la base y se aplica todo en una
 *      sola transaccion.
 *
 * Reglas:
 *   - Un campo vacio en el CSV no borra lo que ya hay en la base.
 *   - Los usuarios nuevos se crean con perfil "docente" y una clave de Toba
 *     aleatoria (entran con su cuenta Huayca). No se manejan claves.
 *   - A quien no esta en el CSV no se lo toca.
 *   - Solo para el perfil admin (permiso del item y control aca).
 *
 * Estilos: www/css/huayca.css, seccion "Importar usuarios". Archivo en ASCII:
 * los acentos de la interfaz van como entidades HTML.
 */

class catedras_importar_usuarios
{
	const PERFIL_NUEVOS = 'docente';
	const MAX_BYTES = 1048576;
	const MEMORIA = 'importar_usuarios_filas';

	static function h($texto)
	{
		return htmlspecialchars((string) $texto, ENT_QUOTES, 'ISO-8859-1');
	}

	static function db()
	{
		return toba::instancia()->get_db();
	}

	// ------------------------------------------------------------------
	// Lectura del CSV
	// ------------------------------------------------------------------

	/** Texto del archivo en LATIN1, sin BOM. */
	static function a_latin1($bytes)
	{
		if (substr($bytes, 0, 3) === "\xEF\xBB\xBF") {
			$bytes = substr($bytes, 3);
		}
		if (preg_match('/[\x80-\xFF]/', $bytes) && mb_check_encoding($bytes, 'UTF-8')) {
			return mb_convert_encoding($bytes, 'ISO-8859-1', 'UTF-8');
		}
		if (preg_match('/[\x80-\x9F]/', $bytes)) {
			// Windows-1252 (Excel): comillas y guiones tipograficos fuera de LATIN1
			return mb_convert_encoding(mb_convert_encoding($bytes, 'UTF-8', 'Windows-1252'), 'ISO-8859-1', 'UTF-8');
		}
		return $bytes;
	}

	/**
	 * @return array(filas, error) filas: lista de array(linea, usuario, dni, nombre, email)
	 */
	static function leer_csv($ruta)
	{
		$texto = self::a_latin1(file_get_contents($ruta));
		$lineas = preg_split('/\r\n|\r|\n/', $texto);
		$primera = isset($lineas[0]) ? $lineas[0] : '';
		$sep = (substr_count($primera, ';') > substr_count($primera, ',')) ? ';' : ',';

		$alias = array(
			'usuario' => 'usuario', 'legajo' => 'usuario',
			'dni' => 'dni', 'documento' => 'dni', 'ciu' => 'dni',
			'nombre' => 'nombre', 'apellido y nombre' => 'nombre', 'apellido_nombre' => 'nombre',
			'email' => 'email', 'mail' => 'email', 'correo' => 'email',
		);
		$cab = array();
		foreach (str_getcsv($primera, $sep) as $i => $c) {
			$c = strtolower(trim($c));
			if (isset($alias[$c])) {
				$cab[$alias[$c]] = $i;
			}
		}
		if (!isset($cab['usuario']) || !isset($cab['dni'])) {
			return array(array(), 'El archivo tiene que tener al menos las columnas <strong>usuario</strong> (o legajo) y <strong>dni</strong> en la primera fila.');
		}

		$filas = array();
		for ($n = 1; $n < count($lineas); $n++) {
			if (trim($lineas[$n]) === '') {
				continue;
			}
			$c = str_getcsv($lineas[$n], $sep);
			$v = function ($campo) use ($c, $cab) {
				return (isset($cab[$campo]) && isset($c[$cab[$campo]])) ? trim($c[$cab[$campo]]) : '';
			};
			$filas[] = array(
				'linea' => $n + 1,
				'usuario' => $v('usuario'),
				'dni' => preg_replace('/[\s.]/', '', $v('dni')),
				'nombre' => preg_replace('/\s+/', ' ', $v('nombre')),
				'email' => $v('email'),
			);
		}
		if (!$filas) {
			return array(array(), 'El archivo no tiene filas de datos.');
		}
		return array($filas, null);
	}

	// ------------------------------------------------------------------
	// Plan: que va a pasar con cada fila (se calcula siempre contra la base)
	// ------------------------------------------------------------------

	static function planificar($filas)
	{
		$db = self::db();
		$plan = array('nuevos' => array(), 'actualizar' => array(), 'iguales' => array(), 'problemas' => array());

		$existentes = array();
		foreach ($db->consultar('SELECT usuario, nombre, email, ciu FROM apex_usuario') as $u) {
			$existentes[$u['usuario']] = $u;
		}
		$dni_de = array();
		foreach ($existentes as $u) {
			if (trim((string) $u['ciu']) !== '') {
				$dni_de[trim($u['ciu'])][] = $u['usuario'];
			}
		}
		$con_perfil = array();
		foreach ($db->consultar("SELECT DISTINCT usuario FROM apex_usuario_proyecto WHERE proyecto = 'catedras'") as $u) {
			$con_perfil[$u['usuario']] = true;
		}

		$vistos_usuario = array();
		$vistos_dni = array();
		foreach ($filas as $f) {
			$prob = array();
			if ($f['usuario'] === '') {
				$prob[] = 'falta el usuario';
			}
			if ($f['dni'] === '') {
				$prob[] = 'falta el DNI';
			} elseif (!preg_match('/^\d{6,9}$/', $f['dni'])) {
				$prob[] = 'el DNI no es un n&uacute;mero v&aacute;lido';
			}
			if ($f['usuario'] !== '' && isset($vistos_usuario[$f['usuario']])) {
				$prob[] = 'usuario repetido en el archivo (fila ' . $vistos_usuario[$f['usuario']] . ')';
			}
			if ($f['dni'] !== '' && isset($vistos_dni[$f['dni']])) {
				$prob[] = 'DNI repetido en el archivo (fila ' . $vistos_dni[$f['dni']] . ')';
			}
			if ($f['dni'] !== '' && isset($dni_de[$f['dni']])) {
				$otros = array_diff($dni_de[$f['dni']], array($f['usuario']));
				if ($otros) {
					$prob[] = 'el DNI ya lo tiene el usuario ' . self::h(implode(', ', $otros));
				}
			}
			// Puede haber mas de una direccion (separadas por espacio, coma o punto y coma)
			foreach (preg_split('/[\s,;]+/', $f['email'], -1, PREG_SPLIT_NO_EMPTY) as $mail) {
				if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
					$prob[] = 'el email ' . self::h($mail) . ' no es v&aacute;lido';
				}
			}
			if ($f['usuario'] !== '') {
				$vistos_usuario += array($f['usuario'] => $f['linea']);
			}
			if ($f['dni'] !== '') {
				$vistos_dni += array($f['dni'] => $f['linea']);
			}

			if ($prob) {
				$plan['problemas'][] = array('fila' => $f, 'motivos' => $prob);
				continue;
			}
			if (!isset($existentes[$f['usuario']])) {
				$plan['nuevos'][] = array('fila' => $f);
				continue;
			}
			$actual = $existentes[$f['usuario']];
			$cambios = array();
			foreach (array('dni' => 'ciu', 'nombre' => 'nombre', 'email' => 'email') as $campo => $col) {
				$nuevo = $f[$campo];
				$viejo = trim((string) $actual[$col]);
				if ($nuevo !== '' && $nuevo !== $viejo) {
					$cambios[$col] = array($viejo, $nuevo);
				}
			}
			$falta_perfil = !isset($con_perfil[$f['usuario']]);
			if ($cambios || $falta_perfil) {
				$plan['actualizar'][] = array('fila' => $f, 'cambios' => $cambios, 'falta_perfil' => $falta_perfil);
			} else {
				$plan['iguales'][] = array('fila' => $f);
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
		$hasher = new toba_hash(apex_pa_algoritmo_hash);
		$db->abrir_transaccion();
		try {
			foreach ($plan['nuevos'] as $p) {
				$f = $p['fila'];
				$clave = $hasher->hash(bin2hex(random_bytes(24)));
				$db->ejecutar('INSERT INTO apex_usuario (usuario, nombre, autentificacion, clave, email, ciu) VALUES ('
					. $db->quote($f['usuario']) . ', ' . $db->quote($f['nombre']) . ', ' . $db->quote(apex_pa_algoritmo_hash) . ', '
					. $db->quote($clave) . ', ' . ($f['email'] !== '' ? $db->quote($f['email']) : 'NULL') . ', ' . $db->quote($f['dni']) . ')');
				$db->ejecutar("INSERT INTO apex_usuario_proyecto (proyecto, usuario_grupo_acc, usuario) VALUES ('catedras', "
					. $db->quote(self::PERFIL_NUEVOS) . ', ' . $db->quote($f['usuario']) . ')');
			}
			foreach ($plan['actualizar'] as $p) {
				$f = $p['fila'];
				if ($p['cambios']) {
					$sets = array();
					foreach ($p['cambios'] as $col => $par) {
						$sets[] = $col . ' = ' . $db->quote($par[1]);
					}
					$db->ejecutar('UPDATE apex_usuario SET ' . implode(', ', $sets) . ' WHERE usuario = ' . $db->quote($f['usuario']));
				}
				if ($p['falta_perfil']) {
					$db->ejecutar("INSERT INTO apex_usuario_proyecto (proyecto, usuario_grupo_acc, usuario) VALUES ('catedras', "
						. $db->quote(self::PERFIL_NUEVOS) . ', ' . $db->quote($f['usuario']) . ')');
				}
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('IMPORTAR USUARIOS: ' . $e->getMessage());
			throw $e;
		}
		toba::logger()->notice('IMPORTAR USUARIOS por ' . toba::usuario()->get_id() . ': '
			. count($plan['nuevos']) . ' nuevos, ' . count($plan['actualizar']) . ' actualizados');
	}

	// ------------------------------------------------------------------
	// Pantalla
	// ------------------------------------------------------------------

	static function url()
	{
		return toba::vinculador()->get_url();
	}

	/**
	 * Token anti-CSRF para el formulario de ESTA carga de pagina. Toba lo valida
	 * contra la pagina desde la que se envia; enviar_csrf_hidden() reutiliza el de
	 * una carga anterior si quedo guardado, y entonces el POST se rechaza.
	 */
	static function campo_csrf()
	{
		echo toba_form::hidden(apex_sesion_csrt, toba::memoria()->fijar_csrf_token(true));
	}

	static function cabecera()
	{
		echo '<div class="hy-inicio hy-import">';
		// El titulo ya lo pone la barra de la operacion: aca solo la explicacion
		echo '<header class="hy-inicio-saludo">';
		echo '<p class="hy-inicio-bajada">Cargar o actualizar responsables de c&aacute;tedra desde un CSV. Primero se ve qu&eacute; va a pasar; no se guarda nada hasta confirmar.</p>';
		echo '</header>';
	}

	static function aviso($texto, $tipo = 'error')
	{
		echo '<div class="hy-import-aviso hy-import-aviso-' . $tipo . '">' . $texto . '</div>';
	}

	static function form_subir()
	{
		echo '<section class="hy-tarjeta">';
		echo '<h2>1. Elegir el archivo</h2>';
		echo '<p>CSV con una fila de encabezado y las columnas <code>usuario</code> (o <code>legajo</code>), <code>dni</code>, <code>nombre</code> y <code>email</code>. Puede venir de Excel o de una planilla de Google: la codificaci&oacute;n y el separador (<code>,</code> o <code>;</code>) se detectan solos.</p>';
		echo '<form method="post" enctype="multipart/form-data" action="' . self::h(self::url()) . '" class="hy-import-form">';
		self::campo_csrf();
		echo '<input type="hidden" name="paso" value="previa">';
		echo '<input type="file" name="csv" accept=".csv,.txt,text/csv" required>';
		echo '<button type="submit" class="hy-boton-primario">Ver qu&eacute; va a pasar</button>';
		echo '</form>';
		echo '<p class="hy-nota">Los usuarios nuevos se crean con perfil <strong>docente</strong> y entran con su cuenta Huayca. Un campo vac&iacute;o en el CSV no borra lo que ya est&aacute; cargado. A quien no figura en el archivo no se lo toca.</p>';
		echo '</section>';
	}

	static function resumen($plan)
	{
		$items = array(
			'nuevos' => array('Nuevos', 'hy-chip-nuevo'),
			'actualizar' => array('A actualizar', 'hy-chip-cambio'),
			'iguales' => array('Sin cambios', 'hy-chip-igual'),
			'problemas' => array('Con problemas', 'hy-chip-problema'),
		);
		echo '<div class="hy-import-resumen">';
		foreach ($items as $k => $d) {
			echo '<div class="hy-chip ' . $d[1] . '"><strong>' . count($plan[$k]) . '</strong> ' . $d[0] . '</div>';
		}
		echo '</div>';
	}

	static function tabla_filas($titulo, $lista, $columna_extra = null)
	{
		if (!$lista) {
			return;
		}
		echo '<details class="hy-import-grupo"' . ($columna_extra ? ' open' : '') . '><summary>' . $titulo . ' (' . count($lista) . ')</summary>';
		echo '<table class="hy-import-tabla"><thead><tr><th>Fila</th><th>Usuario</th><th>DNI</th><th>Nombre</th><th>Email</th>'
			. ($columna_extra ? '<th>' . $columna_extra . '</th>' : '') . '</tr></thead><tbody>';
		foreach ($lista as $p) {
			$f = $p['fila'];
			echo '<tr><td>' . (int) $f['linea'] . '</td><td>' . self::h($f['usuario']) . '</td><td>' . self::h($f['dni']) . '</td><td>'
				. self::h($f['nombre']) . '</td><td>' . self::h($f['email']) . '</td>';
			if ($columna_extra) {
				echo '<td>';
				if (isset($p['motivos'])) {
					echo implode('<br>', $p['motivos']);
				}
				if (isset($p['cambios'])) {
					$nombres = array('ciu' => 'DNI', 'nombre' => 'Nombre', 'email' => 'Email');
					foreach ($p['cambios'] as $col => $par) {
						echo '<div><span class="hy-import-campo">' . $nombres[$col] . ':</span> <del>' . ($par[0] !== '' ? self::h($par[0]) : '(vac&iacute;o)') . '</del> &rarr; <ins>' . self::h($par[1]) . '</ins></div>';
					}
					if (!empty($p['falta_perfil'])) {
						echo '<div><span class="hy-import-campo">Perfil:</span> <ins>se agrega docente</ins></div>';
					}
				}
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table></details>';
	}

	static function previa($plan)
	{
		echo '<section class="hy-tarjeta">';
		echo '<h2>2. Revisar</h2>';
		self::resumen($plan);
		self::tabla_filas('Con problemas: no se cargan', $plan['problemas'], 'Motivo');
		self::tabla_filas('A actualizar', $plan['actualizar'], 'Cambios');
		self::tabla_filas('Nuevos', $plan['nuevos']);
		self::tabla_filas('Sin cambios', $plan['iguales']);

		$hay_algo = count($plan['nuevos']) + count($plan['actualizar']);
		echo '<div class="hy-import-acciones">';
		if ($hay_algo) {
			echo '<form method="post" action="' . self::h(self::url()) . '">';
			self::campo_csrf();
			echo '<input type="hidden" name="paso" value="confirmar">';
			echo '<button type="submit" class="hy-boton-primario">Confirmar: crear ' . count($plan['nuevos']) . ' y actualizar ' . count($plan['actualizar']) . '</button>';
			echo '</form>';
		} else {
			echo '<p>No hay nada para cargar.</p>';
		}
		echo '<a class="hy-boton-secundario" href="' . self::h(self::url()) . '">Elegir otro archivo</a>';
		echo '</div>';
		if ($plan['problemas']) {
			echo '<p class="hy-nota">Las filas con problemas se ignoran. Si las correg&iacute;s en el archivo, pod&eacute;s volver a subirlo: lo que ya est&eacute; cargado va a aparecer como &laquo;sin cambios&raquo;.</p>';
		}
		echo '</section>';
	}

	static function ejecutar()
	{
		$perfiles = array_map('strtolower', toba::usuario()->get_perfiles_funcionales());
		self::cabecera();
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
				self::aviso('El archivo es demasiado grande (m&aacute;ximo 1 MB).');
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
				self::aviso('Se perdi&oacute; la vista previa (por ejemplo, porque pas&oacute; mucho tiempo). Volv&eacute; a subir el archivo.');
			} else {
				$plan = self::planificar($filas);	// de nuevo, contra la base actual
				try {
					self::aplicar($plan);
					toba::memoria()->eliminar_dato_operacion(self::MEMORIA);
					self::aviso('Listo: se crearon <strong>' . count($plan['nuevos']) . '</strong> usuarios y se actualizaron <strong>' . count($plan['actualizar']) . '</strong>.'
						. ($plan['problemas'] ? ' ' . count($plan['problemas']) . ' filas con problemas no se cargaron.' : ''), 'ok');
				} catch (Exception $e) {
					self::aviso('No se pudo guardar y no se cambi&oacute; nada. Detalle en el log de Toba.');
				}
			}
		}
		self::form_subir();
		echo '</div>';
	}
}

catedras_importar_usuarios::ejecutar();
