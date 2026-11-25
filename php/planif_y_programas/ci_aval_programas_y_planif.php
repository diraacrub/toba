<?php
require_once 'planif_y_programas/ci_programas_y_planif.php';

/**
 * Aval del programa y la planificacion juntos, desde 2027 (ingenierias::DESDE).
 *
 * Como el docente los presenta juntos en Programas y Planificaciones, el
 * departamento (DEPARTAMENTOS > Aval Programas y Planificaciones, item 3539)
 * y la Secretaria Academica (SECRETARIA ACADEMICA > SAC Programas y
 * Planificaciones, item 3540) los ven juntos (las pantallas "ver" de la
 * operacion del docente) y los avalan con una sola decision, que vale para
 * los dos con las mismas firmas y el mismo comentario:
 *
 *   docente -> depto -> sac -> aprobado
 *
 *   - Departamento: firmar y enviar a SAC (firma_dto, firma_dto_planif) o
 *     devolver al docente (se borra firma_doc / firma_doc_planif).
 *   - SAC: firmar y aprobar (firma_sac, firma_sac_planif) o devolver al
 *     departamento (se borra firma_dto / firma_dto_planif).
 *
 * Hasta 2026 cada cosa sigue en su operacion (Aval Programas, Aval
 * Planificaciones, SAC Aval Programas, SAC Planificaciones).
 *
 * Archivo en ASCII: los textos con tildes van con escapes LATIN1 (\xf3 = o con tilde).
 */
abstract class ci_aval_programas_y_planif extends ci_programas_y_planif
{
	/** Lo que llego del formulario de aval en este pedido. */
	protected $aval_recibido = null;

	/** 'depto' o 'sac': el estado que esta operacion avala. */
	abstract protected function rol();

	/** Los programas para avalar ($pendientes) o los ya avalados, desde 2027. */
	abstract protected function listado($pendientes);

	/** Opciones del aval: estado nuevo => texto. La primera, quedarse como esta. */
	abstract protected function opciones();

	abstract protected function titulos();

	protected function es_admin()
	{
		return (bool) array_intersect(toba::usuario()->get_perfiles_funcionales(), array('admin'));
	}

	protected function estados_legibles($datos)
	{
		$textos = array('docente' => 'Borrador', 'depto' => "En revisi\xf3n del Departamento",
			'sac' => "En revisi\xf3n de la Secretar\xeda Acad\xe9mica", 'aprobado' => 'Aprobado');
		foreach ($datos as $k => $r) {
			if (isset($r['estado'], $textos[$r['estado']])) {
				$datos[$k]['estado'] = $textos[$r['estado']];
			}
		}
		return $datos;
	}

	//---- Cuadros -----------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		list($pendientes, $avalados) = $this->titulos();
		$cuadro->set_titulo($pendientes);
		$cuadro->set_datos($this->estados_legibles($this->listado(true)));
	}

	function conf__enviados(catedras_ei_cuadro $cuadro)
	{
		list($pendientes, $avalados) = $this->titulos();
		$cuadro->set_titulo($avalados);
		$cuadro->set_datos($this->estados_legibles($this->listado(false)));
	}

	protected function seleccionar($datos)
	{
		$this->id_programa_seleccionado = $datos['id_programa'];
		toba::memoria()->set_dato_operacion('id_programa_seleccionado', $this->id_programa_seleccionado);
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_ver');
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->seleccionar($datos);
	}

	function evt__enviados__seleccion($datos)
	{
		$this->seleccionar($datos);
	}

	//---- El aval -----------------------------------------------------------------

	/** El programa elegido con su planificacion (estados, firmas, comentarios). */
	protected function programa_aval()
	{
		$id = $this->ver_obtener_id_programa();
		if (empty($id)) {
			return false;
		}
		return toba::db('catedras')->consultar_fila("SELECT p.id_programa, p.ano_academico, p.estado,
				p.comentarios, p.firma_doc, p.firma_dto, p.firma_sac,
				pl.id_planificacion, pl.estado_planificacion, pl.firma_doc_planif,
				pl.firma_dto_planif, pl.firma_sac_planif
			FROM programas p
			LEFT JOIN planificaciones pl ON pl.id_prog_planif = p.id_programa
			WHERE p.id_programa = " . (int) $id);
	}

	protected function firmas_html($p)
	{
		$filas = array();
		foreach (array('firma_doc' => 'Docente', 'firma_dto' => 'Departamento', 'firma_sac' => 'Secretar&iacute;a Acad&eacute;mica') as $c => $quien) {
			$valor = trim((string) $p[$c]);
			$filas[] = '<div><strong>' . $quien . ':</strong> '
				. ($valor !== '' ? htmlspecialchars($valor, ENT_QUOTES, 'ISO-8859-1') : '<em>sin firmar</em>') . '</div>';
		}
		return implode('', $filas);
	}

	/** El aval se puede dar si el programa esta en el estado de esta operacion. */
	protected function puede_avalar($p)
	{
		return $p && $p['estado'] === $this->rol() && (int) $p['ano_academico'] >= ingenierias::DESDE;
	}

	function conf__aval(toba_ei_formulario $form)
	{
		$p = $this->programa_aval();
		if (!$p) {
			return;
		}
		$form->set_datos(array('id_programa' => $p['id_programa'], 'id_planificacion' => $p['id_planificacion'],
			'comentarios' => $p['comentarios'], 'firmas' => $this->firmas_html($p), 'estado' => $p['estado']));
		if (!$this->puede_avalar($p)) {
			$form->set_solo_lectura();
			$this->pantalla()->eliminar_evento('guardar');
		}
	}

	/** carga_metodo del estado del aval. */
	function get_opciones_aval()
	{
		$p = $this->programa_aval();
		$opciones = $this->puede_avalar($p) ? $this->opciones() : array();
		if ($p && !isset($opciones[$p['estado']])) {
			$legible = $this->estados_legibles(array(array('estado' => $p['estado'])));
			$opciones = array($p['estado'] => $legible[0]['estado']) + $opciones;
		}
		$salida = array();
		foreach ($opciones as $clave => $texto) {
			$salida[] = array('clave' => $clave, 'descripcion' => $texto);
		}
		return $salida;
	}

	function evt__aval__modificacion($datos)
	{
		$this->aval_recibido = $datos;
	}

	function evt__guardar()
	{
		$p = $this->programa_aval();
		if (!$this->puede_avalar($p)) {
			throw new toba_error("Este programa ya no est\xe1 para avalar aqu\xed (otra persona pudo haberlo cambiado).");
		}
		$datos = is_array($this->aval_recibido) ? $this->aval_recibido : array();
		$estado = isset($datos['estado']) ? (string) $datos['estado'] : $this->rol();
		$comentario = isset($datos['comentario']) ? trim((string) $datos['comentario']) : '';
		$opciones = $this->opciones();
		if (!isset($opciones[$estado])) {
			throw new toba_error("Eleg\xed qu\xe9 hacer con el programa y la planificaci\xf3n.");
		}
		$devuelve = in_array($estado, array('docente', 'depto'), true) && $estado !== $this->rol();
		if ($devuelve && $comentario === '') {
			throw new toba_error("Para devolverlos, escrib\xed en el comentario qu\xe9 hay que revisar.");
		}
		if ($estado === $this->rol() && $comentario === '') {
			toba::notificacion()->agregar('No hubo cambios.', 'info');
			$this->resetear();
			return;
		}
		$this->aplicar($p, $estado, $comentario);
		$hechos = array('sac' => "pasaron a la Secretar\xeda Acad\xe9mica", 'aprobado' => 'quedaron aprobados',
			'docente' => 'volvieron al docente', 'depto' => 'volvieron al Departamento');
		toba::notificacion()->agregar($estado === $this->rol()
			? "Se guard\xf3 el comentario."
			: "El programa y la planificaci\xf3n " . $hechos[$estado] . '.', 'info');
		$this->resetear();
	}

	/** Las firmas que cambian: [programas => [col => valor], planificaciones => [...]]. */
	protected function firmas($estado_nuevo)
	{
		$nombre = toba::usuario()->get_nombre();
		$ahora = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
		$ts = $ahora->format('Y-m-d H:i:s');
		switch ($estado_nuevo) {
			case 'sac':
				$firma = "Firmado electr\xf3nicamente por $nombre - Responsable de Departamento/Delegaci\xf3n/Coordinaci\xf3n de Carrera - $ts";
				return array(array('firma_dto' => 'Conformidad del Departamento: ' . $firma), array('firma_dto_planif' => $firma));
			case 'aprobado':
				$firma = "Firmado electr\xf3nicamente por $nombre - Secretar\xeda Acad\xe9mica - $ts";
				return array(array('firma_sac' => "Conformidad de la Secretar\xeda Acad\xe9mica: " . $firma),
					array('firma_sac_planif' => $firma));
			case 'docente':
				return array(array('firma_doc' => ''), array('firma_doc_planif' => ''));
			case 'depto':
				return array(array('firma_dto' => ''), array('firma_dto_planif' => ''));
		}
		return array(array(), array());
	}

	protected function comentario_html($comentario)
	{
		$ahora = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
		return "<div style='border:1px solid #ccc;padding:10px;margin-top:10px;'><strong>"
			. htmlspecialchars(toba::usuario()->get_nombre(), ENT_QUOTES, 'ISO-8859-1') . ' ('
			. htmlspecialchars(toba::usuario()->get_id(), ENT_QUOTES, 'ISO-8859-1') . ') - ' . $ahora->format('Y-m-d H:i:s')
			. "</strong><br><p style='margin:5px 0;'>" . nl2br(htmlspecialchars($comentario, ENT_QUOTES, 'ISO-8859-1'))
			. '</p></div>';
	}

	/**
	 * Aplica el aval al programa y su planificacion, en una transaccion, con
	 * un renglon en movimientos para cada uno. Falla si el programa ya no
	 * esta en el estado de esta operacion.
	 */
	protected function aplicar($p, $estado, $comentario)
	{
		$db = toba::db('catedras');
		list($firmas_prog, $firmas_planif) = $estado === $this->rol() ? array(array(), array()) : $this->firmas($estado);
		$html = $comentario !== '' ? $this->comentario_html($comentario) : '';
		$sets = array('estado = ' . quote($estado));
		foreach ($firmas_prog as $c => $v) {
			$sets[] = "$c = " . quote($v);
		}
		if ($html !== '') {
			$sets[] = "comentarios = COALESCE(comentarios, '') || " . quote($html);
		}
		$sets_planif = array('estado_planificacion = ' . quote($estado));
		foreach ($firmas_planif as $c => $v) {
			$sets_planif[] = "$c = " . quote($v);
		}
		if ($html !== '') {
			$sets_planif[] = "comentarios_planif = COALESCE(comentarios_planif, '') || " . quote($html);
		}
		$usuario = toba::usuario()->get_id();
		$obs = 'Aval conjunto (' . $this->rol() . '): ' . $p['estado'] . ' -> ' . $estado . ($html !== '' ? ', con comentario' : '');
		$db->abrir_transaccion();
		try {
			$n = $db->ejecutar('UPDATE programas SET ' . implode(', ', $sets)
				. ' WHERE id_programa = ' . (int) $p['id_programa'] . ' AND estado = ' . quote($this->rol()));
			if ((int) $n !== 1) {
				throw new toba_error("Este programa ya no est\xe1 para avalar aqu\xed (otra persona pudo haberlo cambiado).");
			}
			$db->ejecutar('UPDATE planificaciones SET ' . implode(', ', $sets_planif)
				. ' WHERE id_prog_planif = ' . (int) $p['id_programa']);
			foreach (array('programas' => $p['id_programa'], 'planificaciones' => $p['id_planificacion']) as $tabla => $id) {
				if (empty($id)) {
					continue;
				}
				$db->ejecutar('INSERT INTO movimientos (nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento,
					usuario_movimiento, observaciones, estado_mov) VALUES (' . quote($tabla) . ', ' . (int) $id
					. ', now(), ' . quote('Aval conjunto') . ', ' . quote($usuario) . ', ' . quote($obs) . ', '
					. quote($estado) . ')');
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			throw $e;
		}
	}
}

/** DEPARTAMENTOS > Aval Programas y Planificaciones (item 3539). */
class ci_aval_depto_programas_y_planif extends ci_aval_programas_y_planif
{
	protected function rol()
	{
		return 'depto';
	}

	protected function titulos()
	{
		return array('Programas y planificaciones para avalar (desde ' . ingenierias::DESDE . ')',
			"Enviados a la Secretar\xeda Acad\xe9mica");
	}

	protected function opciones()
	{
		return array(
			'depto' => "Sigue en revisi\xf3n del Departamento (solo guarda el comentario)",
			'sac' => "Firmar y enviar a la Secretar\xeda Acad\xe9mica",
			'docente' => 'Devolver al docente para revisar',
		);
	}

	protected function listado($pendientes)
	{
		$t = $this->dep('datos')->tabla('programas');
		if ($this->es_admin()) {
			return $pendientes ? $t->get_listado_estado_depto(true) : $t->get_listado_estado_sac_aprobado(true);
		}
		$perfil = str_replace('idiomasextranjeros', 'idiomasextranjerosconpropositosespecificos',
			toba::usuario()->get_perfiles_funcionales());
		$usuario = toba::usuario()->get_id();
		return $pendientes ? $t->get_listado_filtrado_depto($usuario, $perfil, true)
			: $t->get_listado_enviados_depto($usuario, $perfil, true);
	}
}

/** SECRETARIA ACADEMICA > SAC Programas y Planificaciones (item 3540). */
class ci_aval_sac_programas_y_planif extends ci_aval_programas_y_planif
{
	protected function rol()
	{
		return 'sac';
	}

	protected function titulos()
	{
		return array('Programas y planificaciones para aprobar (desde ' . ingenierias::DESDE . ')', 'Aprobados');
	}

	protected function opciones()
	{
		return array(
			'sac' => "Sigue en la Secretar\xeda Acad\xe9mica (solo guarda el comentario)",
			'aprobado' => 'Firmar y aprobar',
			'depto' => 'Devolver al Departamento para revisar',
		);
	}

	/** Los departamentos de cada perfil de Secretaria (como en SAC Aval Programas). */
	protected function deptos_principales()
	{
		$perfiles = toba::usuario()->get_perfiles_funcionales();
		if (in_array('saccrub', $perfiles)) {
			return array("ECOLOG\xcdA", "EXPLOTACI\xd3N DE RECURSOS ACU\xc1TICOS", "MATEM\xc1TICA", "F\xcdSICA", "QU\xcdMICA",
				"BIOLOG\xcdA GENERAL", "BOT\xc1NICA", "EDUCACI\xd3N F\xcdSICA", "DID\xc1CTICA", "ESTAD\xcdSTICA", "ENFERMER\xcdA",
				"INGENIER\xcdA CIVIL", "POL\xcdTICA EDUCACIONAL", "PSICOLOG\xcdA", "GEOLOG\xcdA Y PETR\xd3LEO", "ZOOLOG\xcdA");
		}
		if (in_array('sacfadel', $perfiles)) {
			return array("IDIOMAS EXTRANJEROS CON PROP\xd3SITOS ESPEC\xcdFICOS");
		}
		return array();
	}

	protected function listado($pendientes)
	{
		$t = $this->dep('datos')->tabla('programas');
		if ($this->es_admin()) {
			return $pendientes ? $t->get_listado_estado_sac(true) : $t->get_listado_estado_aprobado(true);
		}
		$usuario = toba::usuario()->get_id();
		$deptos = $this->deptos_principales();
		return $pendientes ? $t->get_listado_filtrado_sac($usuario, $deptos, true)
			: $t->get_listado_enviados_sac($usuario, $deptos, true);
	}
}
