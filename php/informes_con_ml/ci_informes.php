<?php
require_once 'ci_base_operaciones.php';
require_once 'informes/analisis_por_cargo.php';
class ci_informes extends ci_base_operaciones
{
	
	protected $s__datos_filtro;
	protected $s__datos_viejos; // Para guardar los datos antes de modificar  
	
	private $id_informe_seleccionado;
	private $tabla_vacia;
	private $tabla_html;
	private $contador_mensajes=0;
	
	
	



	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{

		
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();

		// Lista de usuarios que no requieren el filtro
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {
			$datos = $this->dep('datos')->tabla('informes')->get_listado_estado_docente();
		} else {
			$datos = $this->dep('datos')->tabla('informes')->get_listado_filtrado($usuario_id);
		}
		
		foreach ($datos as $key => $registro) {
			if ($registro ['estado_informe'] === 'docente') {
				$datos[$key]['estado_informe'] = 'Borrador';        
			}
			if ($registro ['estado_informe'] === 'depto') {
				$datos[$key]['estado_informe'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado_informe'] === 'sac') {
				$datos[$key]['estado_informe'] = 'En revisión de la Secretaría Académica';        
			}
		}
			
		
		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
		
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->id_informe_seleccionado = $datos['id_informe']; // Guardar el ID del informe
		toba::memoria()->set_dato_operacion('id_informe_seleccionado', $this->id_informe_seleccionado); // Almacenar en memoria
		
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
		}
	
	
	//----------- Cuadro enviados --------------------------------------------------------
	
		function conf__enviados(catedras_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		toba::logger()->info("Perfiles funcionales del usuario ($usuario_id): " . implode(', ', $perfiles_funcionales));

		// Lista de usuarios que no requieren el filtro
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {        
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('informes')->get_listado_estado_depto_aprobado();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('informes')->get_listado_enviados($usuario_id);
		}
		
		foreach ($datos as $key => $registro) {
			if ($registro ['estado_informe'] === 'docente') {
				$datos[$key]['estado_informe'] = 'Borrador';        
			}
			if ($registro ['estado_informe'] === 'depto') {
				$datos[$key]['estado_informe'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado_informe'] === 'aprobado') {
				$datos[$key]['estado_informe'] = 'Aprobado';        
			}
		}
			

		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}

		function evt__enviados__seleccion($datos)
	{
		$this->id_informe_seleccionado = $datos['id_informe']; // Guardar el ID del informe seleccionado
		toba::memoria()->set_dato_operacion('id_informe_seleccionado', $this->id_informe_seleccionado); // Almacenar en memoria
		
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_ver');
		}
	
	
	

	//---- Formulario -------------------------------------------------------------------
	//
	// La pantalla de edicion tiene tres componentes: 'formulario' (puntos 1 a 10),
	// el ML 'analisis_por_cargo' (punto 11) y 'form_cierre' (punto 12, comentarios
	// y estado). Sus eventos de modificacion solo guardan lo recibido: las
	// validaciones y el guardado en la tabla informes se hacen en evt__guardar,
	// que Toba dispara despues de los eventos de los tres componentes.

	private $datos_formulario = array();
	private $datos_cierre = array();
	private $filas_por_cargo = array();

	/**
	 * Informe en edicion: lo que trae get_datos_informe (informe + programa + materia)
	 * con lo que hay en memoria en el datos_tabla encima. Se toma el id de la fila
	 * cargada (no de $this->id_informe_seleccionado, que no se conserva entre pedidos).
	 */
	protected function get_datos_edicion()
	{
		$tabla = $this->dep('datos')->tabla('informes');
		if (! $tabla->esta_cargada()) {
			return array();
		}
		$en_memoria = $tabla->get();
		if (! is_array($en_memoria)) {
			return array();
		}
		$datos = array();
		if (isset($en_memoria['id_informe'])) {
			$base = $tabla->get_datos_informe($en_memoria['id_informe']);
			if (is_array($base)) {
				$datos = $base;
			}
		}
		return array_merge($datos, $en_memoria);
	}

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->get_datos_edicion());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	function evt__formulario__modificacion($datos)
	{
		$this->datos_formulario = $datos;
	}

	//---- analisis_por_cargo (ML, punto 11) --------------------------------------------

	function conf__analisis_por_cargo(toba_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos_edicion();
		$valor = isset($datos['analisis_por_cargo']) ? $datos['analisis_por_cargo'] : null;
		$filas = analisis_por_cargo::filas($valor);
		if ($filas === null) {
			toba::notificacion()->warning("Este informe tiene el análisis por cargo (punto 11) guardado en el formato anterior. "
				. "Cárguelo nuevamente en el cuadro: mientras quede vacío se conserva el contenido anterior.");
			$filas = array();
		}
		$form_ml->set_datos($filas);
	}

	function evt__analisis_por_cargo__modificacion($datos)
	{
		$this->filas_por_cargo = is_array($datos) ? $datos : array();
	}

	/**
	 * analisis_por_cargo tal como esta en la base (no en memoria: tras un error
	 * de validacion la memoria puede tener filas que todavia no se guardaron).
	 */
	protected function get_analisis_guardado()
	{
		$tabla = $this->dep('datos')->tabla('informes');
		if (! $tabla->esta_cargada()) {
			return null;
		}
		$fila = $tabla->get();
		if (! is_array($fila) || empty($fila['id_informe'])) {
			return null;
		}
		$base = $tabla->get_datos_informe($fila['id_informe']);
		return (is_array($base) && isset($base['analisis_por_cargo'])) ? $base['analisis_por_cargo'] : null;
	}

	//---- form_cierre (punto 12, comentarios y estado) ---------------------------------

	function conf__form_cierre(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->get_datos_edicion());
		}
	}

	function evt__form_cierre__modificacion($datos)
	{
		$this->datos_cierre = $datos;
	}

	//---- Guardado ---------------------------------------------------------------------

	function evt__guardar()
	{
		$tabla = $this->dep('datos')->tabla('informes');
		$datos = array_merge($this->datos_formulario, $this->datos_cierre);

		// Guardar datos viejos antes de modificar
		if ($tabla->esta_cargada()) {
			$this->s__datos_viejos = $tabla->get();
		} else {
			$this->s__datos_viejos = $datos;
		}

		// Punto 11: filas del ML como JSON en informes.analisis_por_cargo.
		// Si el ML viene vacio y el informe tiene la tabla HTML del formato
		// anterior, no se toca la columna (esos informes se adaptan aparte).
		$filas_por_cargo = $this->filas_por_cargo;
		$json_por_cargo = analisis_por_cargo::a_json($filas_por_cargo);
		$analisis_guardado = $this->get_analisis_guardado();
		if ($json_por_cargo === null && analisis_por_cargo::filas($analisis_guardado) === null) {
			$datos['analisis_por_cargo'] = $analisis_guardado;
		} else {
			$datos['analisis_por_cargo'] = $json_por_cargo;
		}

		// Validación de cantidad de estudiantes. Va antes de la firma y los
		// comentarios: si falla, lo cargado queda en memoria (sin sincronizar)
		// para que el docente no lo pierda al volver a mostrarse la pantalla.
		$inscriptos   = isset($datos['inscriptos']) ? $datos['inscriptos'] : 0;
		$comenzaron   = isset($datos['comenzaron']) ? $datos['comenzaron'] : 0;
		$aprobaron    = isset($datos['aprobaron']) ? $datos['aprobaron'] : 0;
		$desaprobaron = isset($datos['desaprobaron']) ? $datos['desaprobaron'] : 0;
		$abandonaron  = isset($datos['abandonaron']) ? $datos['abandonaron'] : 0;

		$suma_resultados = $aprobaron + $desaprobaron + $abandonaron;

		if ($comenzaron > $inscriptos) {
			$tabla->set($datos);
			throw new toba_error("El número de estudiantes que comenzaron no puede ser mayor que los inscriptos.");
		}

		if ($suma_resultados != $comenzaron) {
			$tabla->set($datos);
			throw new toba_error("La suma de aprobaron, desaprobaron y abandonaron debe ser igual a los que comenzaron.");
		}

		// Lógica de firma electrónica según estado
		if (isset($datos['estado_informe']) && $datos['estado_informe'] === 'depto') {
			$nombre_completo = toba::usuario()->get_nombre();
			$timestamp_obj = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
			$formatted_timestamp = $timestamp_obj->format('Y-m-d H:i:s');
			$datos['firma_doc_inf'] = "Firmado electrónicamente por $nombre_completo - Responsable de Cátedra - $formatted_timestamp";
		}

		// Manejo de comentarios
		$comentario = isset($datos['comentario']) ? $datos['comentario'] : '';
		if (!empty($comentario)) {
			$timestamp = date('Y-m-d H:i:s');
			$usuario_id = toba::usuario()->get_id();
			$nombre_completo = toba::usuario()->get_nombre();

			$nuevo_comentario = "<div style='border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
									<strong>$nombre_completo ($usuario_id) - $timestamp</strong><br>
									<p style='margin: 5px 0;'>$comentario</p>
									</div>";

			// Concatenar el nuevo comentario al contenido existente
			$comentarios_inf = $tabla->get_columna('comentarios_inf');
			$datos['comentarios_inf'] = $comentarios_inf . $nuevo_comentario;
		}

		// Validación de campos obligatorios (al firmar y enviar al Departamento)
		$campos_obligatorios = array(
			'inscriptos', 'causas_abandono_desap', 'caract_grupo',
			'estrategias', 'consideraciones_interior', 'analisis_actividades',
			'suficiencia_adecuacion', 'evaluacion_ays', 'articulacion', 'capacitacion',
			'analisis_por_cargo'
		);

		if (isset($datos['estado_informe']) && $datos['estado_informe'] === 'depto') {
			foreach ($campos_obligatorios as $campo) {

				// Caso especial: "inscriptos"
				if ($campo === 'inscriptos') {
					$valor_inscriptos = isset($datos['inscriptos']) ? $datos['inscriptos'] : null;
					$sin_inscriptos   = isset($datos['sin_inscriptos']) ? $datos['sin_inscriptos'] : 0;

					// Si inscriptos es 0 o vacío y no marcó sin_inscriptos -> falta completar
					if (($valor_inscriptos === '' || $valor_inscriptos === null || $valor_inscriptos == 0) && !$sin_inscriptos) {
						$this->volver_a_borrador($datos);
						throw new toba_error("Debe indicar cantidad de inscriptos o marcar 'sin inscriptos'.");
					}
					continue;
				}

				// Caso especial: "analisis_por_cargo" (ML, punto 11): al menos una fila
				if ($campo === 'analisis_por_cargo') {
					if (empty($filas_por_cargo)) {
						$this->volver_a_borrador($datos);
						throw new toba_error("Falta cargar el análisis por cargo (punto 11): agregue una fila por cada docente de la cátedra.");
					}
					continue;
				}

				// Validación general para los demás campos
				if (empty($datos[$campo]) && (! isset($datos[$campo]) || $datos[$campo] !== '0')) {
					$this->volver_a_borrador($datos);
					throw new toba_error("Falta cargar datos obligatorios, por favor corrija.");
				}
			}
		}

		// Guardar finalmente los datos en informes
		$tabla->set($datos);
		$tabla->sincronizar();
		toba::notificacion()->agregar("Su informe ha sido guardado correctamente", 'info');

		// ---- REGISTRO DE MOVIMIENTOS ----
		$est_mov = isset($datos['estado_informe']) ? $datos['estado_informe'] : "";
		$estado_mov = quote($est_mov);
		$usuario_id = toba::usuario()->get_id();
		$nombre_tabla = quote('informes');
		date_default_timezone_set('America/Argentina/Buenos_Aires');
		$fecha_movimiento = quote(date('Y-m-d H:i:s'));
		$usuario_movimiento = quote($usuario_id);

		$id_informe = isset($datos['id_informe']) ? $datos['id_informe'] : null;
		$id_tabla = quote($id_informe);
		$observaciones_txt = $this->armar_observaciones_inf($this->s__datos_viejos, "Actualización desde formulario");
		$tipo_movimiento = quote('Actualización');
		$observaciones = quote($observaciones_txt);
		$sql = "
			INSERT INTO huayca.movimientos
			(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones,estado_mov)
			VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones, $estado_mov)
		";
		toba::db()->ejecutar($sql);

		$this->resetear();
	}

	/**
	 * Si al enviar falta algo obligatorio, el informe se guarda igual pero como
	 * borrador y sin firma (mismo comportamiento que antes).
	 */
	private function volver_a_borrador($datos)
	{
		$datos['estado_informe'] = 'docente';
		$datos['firma_doc_inf'] = '';
		$tabla = $this->dep('datos')->tabla('informes');
		$tabla->set($datos);
		$this->dep('datos')->sincronizar();
	}


///// funcion VER fromulario planif
	
	
	
	function conf__ver_form_inf(toba_ei_formulario $form)
	{
		
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('informes')->get_datos_informe($this->id_informe_seleccionado));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
		
		toba::notificacion()->info("En esta pantalla solo podrá VER el informe seleccionado.");
	
	}
	
	
	
	
		function conf__ver_form_inf_viejo(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('informes')->get_datos_informe($this->id_informe_seleccionado));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}

		toba::notificacion()->info("En esta sección usted puede VER el informe firmado y enviado.");
	
	}
	//-------------------------
	
	
	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->resetear();
	}

	function evt__eliminar()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	
	// Función auxiliar para armar observaciones de planificaciones
private function armar_observaciones_inf($datos, $accion)
{
	// Campos de planif que sí queremos guardar
	$incluir = array('id_informe', 'id_prog_informe', 'estado_planificacion');
	$txt = "";

	foreach ($datos as $campo => $valor) {
		if (in_array($campo, $incluir)) {

			// Si es id_prog_planif, buscamos la materia relacionada
			if ($campo == 'id_prog_informe' && !empty($valor)) {
				$materia = toba::db('catedras')->consultar("
					SELECT m.nombre_materia, m.cod_carrera, m.plan_ordenanzas, m.cod_guarani,
							p.ano_academico, p.nombre_resp, p.apellido_resp, p.periodo_dictado                    
					FROM programas p
					JOIN materias m ON p.id_materia_prog = m.id_materia
					WHERE p.id_programa = " . quote($valor) . "
				");
				if (!empty($materia)) {
					$info_materia = "nombre_materia: <strong>" . $materia[0]['nombre_materia'] . "</strong><br>"
									. "cod_guarani: <strong>" . $materia[0]['cod_guarani'] . "</strong><br>"
									. "cod_carrera: <strong>" . $materia[0]['cod_carrera'] . "</strong><br>"
									. "plan_ordenanzas: <strong>" . $materia[0]['plan_ordenanzas'] . "</strong><br>"
									. "ano_academico: ".$materia[0]['ano_academico']."<br>"
									. "pariodo_dictado: ".$materia[0]['periodo_dictado']."<br>"                                           
									. "nombre_resp: ".$materia[0]['nombre_resp']."<br>"                                           
									. "apellido_resp: ".$materia[0]['apellido_resp'];
					$valor .= "<br>".$info_materia;
				}
			}

			$txt .= $campo . ": " . $valor . "<br>";
		}
	}

	$txt .= $accion;
	return $txt;
}

}
?>