<?php
/**
 * Punto 11 del informe de catedra: analisis por cargo.
 *
 * Se guarda en informes.analisis_por_cargo como JSON: una lista de objetos con
 * las claves de self::$columnas. Dentro del JSON los textos van en UTF-8
 * escapados como \uXXXX, asi el JSON es ASCII y pasa sin cambios por la base
 * LATIN1. La aplicacion trabaja en LATIN1, pero lo que manda el navegador en
 * una pagina "ISO-8859-1" es en realidad Windows-1252 (comillas y guiones de
 * Word, euro); por eso filas() devuelve y a_json() recibe Windows-1252. Lo que
 * no entra en Windows-1252 (emojis, etc.) el navegador lo manda como "&#N;":
 * a_json() lo guarda como el caracter real y filas() lo devuelve como "&#N;".
 *
 * Los informes cargados antes del ML tenian una tabla HTML; html() la muestra
 * tal cual y desde_html() la convierte en filas (ver deploy/convertir_analisis_por_cargo.php).
 */
class analisis_por_cargo
{
	static $columnas = array(
		'docente', 'cargo', 'dedicacion', 'respeta_plazos', 'respeta_jerarquia',
		'asistencia', 'puntualidad', 'comunicacion_estudiantes', 'comunicacion_depto',
		'trabajo', 'institucionales', 'otro'
	);

	// Columnas que se califican de 1 a 4
	static $criterios = array(
		'respeta_plazos', 'respeta_jerarquia', 'asistencia', 'puntualidad',
		'comunicacion_estudiantes', 'comunicacion_depto', 'trabajo', 'institucionales'
	);

	// Encabezados (HTML, solo ASCII con entidades)
	static $titulos = array(
		'docente' => 'Nombre y Apellido',
		'cargo' => 'Cargo',
		'dedicacion' => 'Ded.',
		'respeta_plazos' => '&iquest;Respeta los plazos administrativos?',
		'respeta_jerarquia' => '&iquest;Respeta la v&iacute;a jer&aacute;rquica para las presentaciones?',
		'asistencia' => 'Asistencia a tareas',
		'puntualidad' => 'Puntualidad en tareas',
		'comunicacion_estudiantes' => 'Comunicaci&oacute;n y acciones hacia estudiantes',
		'comunicacion_depto' => 'Comunicaci&oacute;n y acciones hacia la Dir./Del. dptal.',
		'trabajo' => 'Trabajo en equipo de la c&aacute;tedra',
		'institucionales' => 'Participaci&oacute;n en activ. institucionales',
		'otro' => 'Otros',
	);

	// Cargos del combo del ML y equivalencias de lo escrito a mano en las tablas viejas
	static $cargos = array('AYS', 'AYP', 'JTP', 'PAD', 'PAS', 'PTR');
	static $alias_cargos = array(
		'ASD' => 'JTP', 'ADS' => 'JTP', 'ADSJ' => 'JTP',
		'TIT' => 'PTR', 'PT' => 'PTR', 'ASO' => 'PAS',
	);

	//-------------------------------------------------------------------------
	// JSON <-> filas
	//-------------------------------------------------------------------------

	/**
	 * Valor de la columna -> filas (textos en LATIN1).
	 * array() si esta vacio; null si no es JSON (tabla HTML del formato anterior).
	 */
	static function filas($valor)
	{
		$filas = self::filas_utf8($valor);
		if (! is_array($filas)) {
			return $filas;
		}
		$anterior = mb_substitute_character();
		mb_substitute_character('entity');
		foreach ($filas as $i => $fila) {
			foreach ($fila as $columna => $v) {
				if (is_string($v)) {
					$filas[$i][$columna] = mb_convert_encoding($v, 'Windows-1252', 'UTF-8');
				}
			}
		}
		mb_substitute_character($anterior);
		return $filas;
	}

	/**
	 * Como filas(), pero con los textos en UTF-8 tal como estan en el JSON.
	 */
	protected static function filas_utf8($valor)
	{
		if (! is_string($valor) || trim($valor) === '') {
			return array();
		}
		$filas = json_decode($valor, true);
		if (! is_array($filas)) {
			return null;
		}
		$salida = array();
		foreach ($filas as $fila) {
			if (! is_array($fila)) {
				continue;
			}
			$limpia = array();
			foreach (self::$columnas as $columna) {
				$limpia[$columna] = isset($fila[$columna]) ? $fila[$columna] : null;
			}
			$salida[] = $limpia;
		}
		return $salida;
	}

	/**
	 * Filas (textos en Windows-1252, por ejemplo lo que devuelve el ML) -> JSON.
	 * Devuelve null si no hay filas.
	 */
	static function a_json($filas)
	{
		$salida = array();
		foreach ((array) $filas as $fila) {
			$limpia = array();
			foreach (self::$columnas as $columna) {
				$v = isset($fila[$columna]) ? $fila[$columna] : null;
				if (is_string($v)) {
					$v = trim($v);
					if ($v === '') {
						$v = null;
					} else {
						$v = mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
						// "&#N;" / "&#xN;": caracteres que no entran en Windows-1252
						$v = preg_replace_callback('/&#(x[0-9a-f]+|[0-9]+);/i', function ($m) {
							$n = ($m[1][0] === 'x' || $m[1][0] === 'X') ? hexdec(substr($m[1], 1)) : (int) $m[1];
							return ($n > 0 && $n <= 0x10FFFF) ? mb_chr($n, 'UTF-8') : $m[0];
						}, $v);
					}
				}
				$limpia[$columna] = $v;
			}
			$salida[] = $limpia;
		}
		if (empty($salida)) {
			return null;
		}
		$json = json_encode($salida);
		if ($json === false) {
			throw new toba_error('No se pudo guardar el an' . chr(225) . 'lisis por cargo: ' . json_last_error_msg());
		}
		return $json;
	}

	//-------------------------------------------------------------------------
	// Vista
	//-------------------------------------------------------------------------

	/**
	 * Valor de la columna -> tabla HTML para las pantallas de solo lectura y los reportes.
	 * Si no es JSON (formato anterior) devuelve el HTML guardado tal cual.
	 */
	static function html($valor)
	{
		$filas = self::filas_utf8($valor);
		if ($filas === null) {
			return $valor;
		}
		if (empty($filas)) {
			return '<em>Sin cargar</em>';
		}
		// Solo ASCII (tildes como entidades): el ef_html de Toba pasa el valor por
		// htmlspecialchars() sin charset, que en PHP 8 asume UTF-8 y cambia cada
		// byte LATIN1 por U+FFFD ("Nu\xEF\xBF\xBDez" en vez de "Nu&#241;ez").
		$e = function ($v) {
			$v = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
			return mb_encode_numericentity($v, array(0x80, 0x10FFFF, 0, 0x1FFFFF), 'UTF-8');
		};
		$html = '<table border="1" cellspacing="0" cellpadding="3" class="analisis-por-cargo" '
			. 'style="border-collapse: collapse; width: 100%; text-align: center; font-size: 12px;">'
			. '<tr style="background-color: #f2f2f2;">';
		foreach (self::$columnas as $columna) {
			$sufijo = in_array($columna, self::$criterios) ? '<br />(de 1 a 4)' : '';
			$html .= '<th>' . self::$titulos[$columna] . $sufijo . '</th>';
		}
		$html .= '</tr>';
		foreach ($filas as $fila) {
			$html .= '<tr>';
			foreach (self::$columnas as $columna) {
				$alinear = in_array($columna, array('docente', 'otro')) ? ' style="text-align: left;"' : '';
				$html .= '<td' . $alinear . '>' . $e($fila[$columna]) . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>'
			. '<div style="font-size: 11px; margin-top: 4px;">1 = Muy bueno &nbsp; 2 = Bueno &nbsp; 3 = Malo &nbsp; 4 = No lo tiene en cuenta</div>';
		return $html;
	}

	//-------------------------------------------------------------------------
	// Conversion de las tablas HTML del formato anterior
	//-------------------------------------------------------------------------

	/**
	 * Tabla HTML vieja (texto LATIN1) -> array('filas' => [...] (LATIN1), 'notas' => [...]).
	 * Lo que no se puede interpretar no se pierde: queda con el texto original en 'otro'.
	 */
	static function desde_html($html)
	{
		$doc = new DOMDocument();
		@$doc->loadHTML('<?xml encoding="ISO-8859-1">' . $html);
		$filas = array();
		$notas = array();
		foreach ($doc->getElementsByTagName('tr') as $tr) {
			$celdas = array();
			foreach ($tr->getElementsByTagName('td') as $td) {
				// textContent viene en UTF-8
				$t = str_replace("\xc2\xa0", ' ', $td->textContent);
				$celdas[] = trim(preg_replace('/\s+/u', ' ', $t));
			}
			if (empty($celdas) || implode('', $celdas) === '') {
				continue; // encabezado o fila vacia
			}
			$fila = array_fill_keys(self::$columnas, null);
			$otro = array();

			$fila['docente'] = ($celdas[0] !== '') ? $celdas[0] : null;

			$original = isset($celdas[1]) ? $celdas[1] : '';
			if ($original !== '') {
				list($cargo, $dedicacion, $entendido) = self::interpretar_cargo($original);
				$fila['cargo'] = $cargo;
				$fila['dedicacion'] = $dedicacion;
				if (! $entendido) {
					$otro[] = 'Cargo y Ded. original: ' . $original;
					$notas[] = "cargo '$original' -> " . ($cargo ?: '?') . '/' . ($dedicacion ?: '?');
				}
			}

			foreach (self::$criterios as $i => $columna) {
				$v = isset($celdas[$i + 2]) ? $celdas[$i + 2] : '';
				if (preg_match('/^[1-4]$/', $v)) {
					$fila[$columna] = $v;
				} elseif ($v !== '' && ! in_array($v, array('-', '--'))) {
					$otro[] = html_entity_decode(strip_tags(self::$titulos[$columna]), ENT_QUOTES, 'UTF-8') . ': ' . $v;
					$notas[] = "$columna '$v'";
				}
			}

			// Columna Otros y columnas de mas (algunas tablas se editaron a mano)
			for ($i = 10; $i < count($celdas); $i++) {
				if ($celdas[$i] !== '') {
					$otro[] = $celdas[$i];
				}
			}
			if (count($celdas) < 11) {
				$notas[] = 'fila con ' . count($celdas) . ' columnas';
			}
			if (! empty($otro)) {
				$fila['otro'] = implode(' | ', $otro);
			}

			foreach ($fila as $k => $v) {
				if (is_string($v)) {
					$fila[$k] = mb_convert_encoding($v, 'ISO-8859-1', 'UTF-8');
				}
			}
			$filas[] = $fila;
		}
		return array('filas' => $filas, 'notas' => $notas);
	}

	/**
	 * "PAD-3", "JTP SIMPLE", "PAD III", "TIT EXCL", "ASD3"... -> array(cargo, dedicacion, entendido).
	 * cargo y dedicacion son null si no se pueden interpretar; entendido es false
	 * si quedo texto sin interpretar (ej. "JTP" sin dedicacion si se entiende).
	 */
	static function interpretar_cargo($texto)
	{
		$t = strtoupper(trim($texto));
		if (! preg_match('/^([A-Z]{2,4})(.*)$/', $t, $m)) {
			return array(null, null, false);
		}
		$codigo = $m[1];
		$resto = trim($m[2]);
		if (isset(self::$alias_cargos[$codigo])) {
			$codigo = self::$alias_cargos[$codigo];
		}
		// Codigos pegados a una letra de mas: "ASDI3", "AYPI3"
		if (! in_array($codigo, self::$cargos) && strlen($codigo) == 4) {
			$base = substr($codigo, 0, 3);
			$base = isset(self::$alias_cargos[$base]) ? self::$alias_cargos[$base] : $base;
			if (in_array($base, self::$cargos)) {
				$codigo = $base;
			}
		}
		$cargo = in_array($codigo, self::$cargos) ? $codigo : null;

		$dedicacion = null;
		$r = trim(preg_replace('/[\s\.\-,]+/', ' ', $resto));
		if (preg_match('/^[^0-9]*([1-4])[^0-9]*$/', $r, $d)) {
			$dedicacion = $d[1];          // "-3", " 3", "3, SIMPLE", "EC 3", "I3"
		} elseif (preg_match('/^(III|SIMPLE|S|10 HS+)$/', $r)) {
			$dedicacion = '3';
		} elseif (preg_match('/^(II|PARCIAL)$/', $r)) {
			$dedicacion = '2';
		} elseif (preg_match('/^(I|EXCL|EXCLUSIVA)$/', $r)) {
			$dedicacion = '1';
		}
		$entendido = ($cargo !== null) && ($dedicacion !== null || $r === '');
		return array($cargo, $dedicacion, $entendido);
	}
}
