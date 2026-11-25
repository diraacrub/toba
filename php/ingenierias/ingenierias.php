<?php
/**
 * Las ingenierias en Catedras (deploy/sql/ingenierias_2027.sql).
 *
 * Hasta 2026 sus programas solo se cargaban para asociarles planificacion e
 * informe: no aparecen en los listados de programas y el docente presenta la
 * planificacion y el informe en sus operaciones. Desde 2027 (DESDE) el
 * programa y la planificacion se editan en Programas y Planificaciones, como
 * los de las demas carreras, con campos propios:
 *   - de materias (sin editar): i_bloque_curricular, i_correlativas_anteriores,
 *     i_correlativas_posteriores, i_reconocimiento_t_f;
 *   - de programas: i_propositos, i_competencias_espacio_curricular e
 *     i_competencias_que_aporta (JSON con los codigos de las competencias de
 *     la carrera que el docente marca).
 *
 * Archivo en ASCII.
 */
class ingenierias
{
	/** Desde este anio academico los programas de ingenierias van por la operacion conjunta. */
	const DESDE = 2027;

	static $CARRERAS = array('ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

	/** Los campos propios de cada tabla. */
	static $CAMPOS_MATERIA = array('i_bloque_curricular', 'i_correlativas_anteriores',
		'i_correlativas_posteriores', 'i_reconocimiento_t_f');
	static $CAMPOS_PROGRAMA = array('i_propositos', 'i_competencias_espacio_curricular',
		'i_competencias_que_aporta');

	/** Los del programa que hacen falta para enviarlo al departamento. */
	static $OBLIGATORIOS = array('i_propositos', 'i_competencias_espacio_curricular',
		'i_competencias_que_aporta');

	static $ETIQUETAS = array(
		'i_bloque_curricular' => 'Bloque curricular',
		'i_correlativas_anteriores' => 'Correlativas anteriores',
		'i_correlativas_posteriores' => 'Correlativas posteriores',
		'i_reconocimiento_t_f' => 'Reconocimiento T/F',
		'i_propositos' => 'Prop&oacute;sitos',
		'i_competencias_espacio_curricular' => 'Competencias del espacio curricular',
		'i_competencias_que_aporta' => 'Competencias de la carrera que aporta',
	);

	static function es($cod_carrera)
	{
		return in_array(strtoupper(trim((string) $cod_carrera)), self::$CARRERAS, true);
	}

	/** El programa lleva los campos de ingenierias: una ingenieria desde DESDE. */
	static function con_campos($cod_carrera, $ano_academico)
	{
		return self::es($cod_carrera) && (int) $ano_academico >= self::DESDE;
	}

	/**
	 * Condicion SQL para los listados de programas: las ingenierias solo desde
	 * DESDE. $m es el alias de materias y $p el de programas.
	 */
	static function sql_visible($m = 't_m', $p = 't_p')
	{
		return "($m.cod_carrera NOT LIKE 'I%' OR " . self::sql_desde($p) . ')';
	}

	/**
	 * Condicion SQL: el programa es de DESDE en adelante (o, con $desde false,
	 * de antes). programas.ano_academico es texto ('2027'): se compara como
	 * texto, que con anios de cuatro cifras da lo mismo.
	 */
	static function sql_desde($p = 't_p', $desde = true)
	{
		return "$p.ano_academico " . ($desde ? '>=' : '<') . " '" . (int) self::DESDE . "'";
	}

	/**
	 * Las competencias de cada carrera (libro rojo de CONFEDI): codigo =>
	 * texto. SIMULADAS hasta tener las de cada carrera: los codigos son los que
	 * quedan guardados, asi que al cargar las reales conviene conservar los
	 * que ya se usaron o convertir lo guardado.
	 */
	static function competencias($cod_carrera)
	{
		$cod = strtoupper(trim((string) $cod_carrera));
		if (!self::es($cod)) {
			return array();
		}
		$lista = array();
		for ($i = 1; $i <= 3; $i++) {
			$lista["CG$i"] = "Competencia generica $i (simulada)";
		}
		for ($i = 1; $i <= 4; $i++) {
			$lista["CE$i"] = "Competencia especifica $i de $cod (simulada)";
		}
		return $lista;
	}

	/** Opciones para el checkbox (carga por metodo del ef): codigo => texto. */
	static function opciones_competencias($cod_carrera)
	{
		return self::competencias($cod_carrera);
	}

	/** Lo guardado (JSON) -> lista de codigos. */
	static function codigos($json)
	{
		if (is_array($json)) {
			return array_values(array_filter(array_map('strval', $json), 'strlen'));
		}
		$json = trim((string) $json);
		if ($json === '') {
			return array();
		}
		$lista = json_decode($json, true);
		return is_array($lista) ? array_values(array_filter(array_map('strval', $lista), 'strlen')) : array();
	}

	/** Codigos marcados -> JSON para guardar (solo los de la carrera, en su orden). */
	static function a_json($codigos, $cod_carrera)
	{
		$marcados = self::codigos($codigos);
		$validos = array();
		foreach (array_keys(self::competencias($cod_carrera)) as $codigo) {
			if (in_array((string) $codigo, $marcados, true)) {
				$validos[] = (string) $codigo;
			}
		}
		return json_encode($validos);
	}

	/**
	 * Las competencias marcadas, para mostrar o imprimir: una lista HTML
	 * (codigos que ya no estan en la lista, tal cual). '' si no hay.
	 */
	static function competencias_html($json, $cod_carrera)
	{
		$todas = self::competencias($cod_carrera);
		$items = array();
		foreach (self::codigos($json) as $codigo) {
			$texto = isset($todas[$codigo]) ? $todas[$codigo] : $codigo;
			$items[] = '<li>' . htmlspecialchars($texto, ENT_QUOTES, 'ISO-8859-1') . '</li>';
		}
		return $items ? '<ul>' . implode('', $items) . '</ul>' : '';
	}

	/**
	 * Para los formularios de solo lectura (Aval Programas, SAC, Imprimir):
	 * muestra la seccion de ingenierias si el programa la lleva y si no
	 * desactiva sus efs. Devuelve $datos con las competencias como lista HTML
	 * (el ef es un ef_fijo).
	 */
	static function conf_vista($form, $datos)
	{
		$con = is_array($datos) && self::con_campos(
			isset($datos['cod_carrera']) ? $datos['cod_carrera'] : '',
			isset($datos['ano_academico']) ? $datos['ano_academico'] : 0);
		if ($form instanceof catedras_ei_formulario) {
			$form->mostrar_seccion('ingenierias', $con);
		}
		if (!$con) {
			$presentes = array();
			foreach (array_merge(self::$CAMPOS_MATERIA, self::$CAMPOS_PROGRAMA) as $campo) {
				if ($form->existe_ef($campo)) {
					$presentes[] = $campo;
				}
			}
			if ($presentes) {
				$form->desactivar_efs($presentes);
			}
			return $datos;
		}
		$datos['i_competencias_que_aporta'] = self::competencias_html(
			isset($datos['i_competencias_que_aporta']) ? $datos['i_competencias_que_aporta'] : '',
			$datos['cod_carrera']);
		return $datos;
	}

	/**
	 * $datos sin los campos de ingenierias: los formularios de solo lectura
	 * no los guardan (el de competencias tiene HTML para mostrar).
	 */
	static function sin_campos($datos)
	{
		foreach (array_merge(self::$CAMPOS_MATERIA, self::$CAMPOS_PROGRAMA) as $campo) {
			unset($datos[$campo]);
		}
		return $datos;
	}

	/**
	 * Los campos de ingenierias para un impreso: [etiqueta => html]. Vacio si
	 * el programa no los lleva. $datos tiene las columnas de programas y de
	 * materias (cod_carrera, ano_academico e i_*).
	 */
	static function secciones_impresion($datos)
	{
		$cod = isset($datos['cod_carrera']) ? $datos['cod_carrera'] : '';
		$ano = isset($datos['ano_academico']) ? $datos['ano_academico'] : 0;
		if (!self::con_campos($cod, $ano)) {
			return array();
		}
		$salida = array();
		foreach (array_merge(self::$CAMPOS_MATERIA, self::$CAMPOS_PROGRAMA) as $campo) {
			$valor = isset($datos[$campo]) ? $datos[$campo] : '';
			if ($campo === 'i_competencias_que_aporta') {
				$html = self::competencias_html($valor, $cod);
			} else {
				$html = nl2br(htmlspecialchars((string) $valor, ENT_QUOTES, 'ISO-8859-1'));
			}
			$salida[self::$ETIQUETAS[$campo]] = $html;
		}
		return $salida;
	}
}
