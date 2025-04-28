<?php
class ci_planificaciones extends catedras_ci
{
	protected $s__datos_filtro;
	
	private $id_planificacion_seleccionada;
	private $tabla_ec_vacia; 
	private $tabla_html_ec;
	
	private $tabla_dh_vacia;
	private $tabla_html_dh;
	
	



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
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_estado_docente();
		} else {
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_filtrado($usuario_id);
		}
		
		foreach ($datos as $key => $registro) {
			if ($registro ['estado_planificacion'] === 'docente') {
				$datos[$key]['estado_planificacion'] = 'Borrador';        
			}
			if ($registro ['estado_planificacion'] === 'depto') {
				$datos[$key]['estado_planificacion'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado_planificacion'] === 'sac') {
				$datos[$key]['estado_planificacion'] = 'En revisión de la Secretaría Académica';        
			}
		}
			
		
		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
		
		
		
		
		//if (isset($this->s__datos_filtro)) {
		//    $cuadro->set_datos($this->dep('datos')->tabla('planificaciones')->get_listado($this->s__datos_filtro));
		//} else {
		//    $cuadro->set_datos($this->dep('datos')->tabla('planificaciones')->get_listado());
		//}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->id_planificacion_seleccionada = $datos['id_planificacion']; // Guardar el ID de la planif seleccionada
		toba::memoria()->set_dato_operacion('id_planificacion_seleccionada', $this->id_planificacion_seleccionada); // Almacenar en memoria
		
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
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_estado_depto_aprobado();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_enviados($usuario_id);
		}
		foreach ($datos as $key => $registro) {
			if ($registro ['estado_planificacion'] === 'docente') {
				$datos[$key]['estado_planificacion'] = 'Borrador';        
			}
			if ($registro ['estado_planificacion'] === 'depto') {
				$datos[$key]['estado_planificacion'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado_planificacion'] === 'aprobado') {
				$datos[$key]['estado_planificacion'] = 'Aprobado';        
			}
		}
			

		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}


	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('planificaciones')->get_datos_planificacion($this->id_planificacion_seleccionada));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}

		toba::notificacion()->info("Completar sin alterar la estructura de los cuadros.Gracias");
	
	}

	
	function evt__formulario__modificacion($datos)
{
	// Definición forzada a UTF-8 para las tablas “vacías”
$tabla_ec_vacia = utf8_encode("Apellido Nombre Legajo Cargo y Ded.");
$tabla_dh_vacia = utf8_encode("hr. LUNES MARTES MIERCOLES JUEVES VIERNES SABADO T P TP L T P TP L T P TP L T P TP L T P TP L T P TP L 08 09 10 11 12 13 14 15 16 17 18 19 20 21");
$tabla_hc_vacia = utf8_encode("Docente Legajo Día y Horas Lugar");
$tabla_ot_vacia = utf8_encode("Docente INVESTIGACIÓN EXTENSIÓN GOBIERNO OTRAS (Especificar) Nombre y apellido Nombre del Proyecto Hs. sem Nombre del Proyecto Hs. sem TAREA Hs. sem TAREA Hs. sem");
$tabla_bp_vacia = utf8_encode("Título Autores Editorial Edición Biblioteca (SI/NO) ISBN");
	// Limpiar tablas vacías
$texto_dh_vacia = html_entity_decode($tabla_dh_vacia, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_dh_vacia = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_dh_vacia);
$texto_dh_vacia = preg_replace('/\s+/u', ' ', $texto_dh_vacia);
$texto_dh_vacia = trim($texto_dh_vacia);

$texto_ec_vacia = html_entity_decode($tabla_ec_vacia, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_ec_vacia = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_ec_vacia);
$texto_ec_vacia = preg_replace('/\s+/u', ' ', $texto_ec_vacia);
$texto_ec_vacia = trim($texto_ec_vacia);

$texto_hc_vacia = html_entity_decode($tabla_hc_vacia, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_hc_vacia = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_hc_vacia);
$texto_hc_vacia = preg_replace('/\s+/u', ' ', $texto_hc_vacia);
$texto_hc_vacia = trim($texto_hc_vacia);

$texto_ot_vacia = html_entity_decode($tabla_ot_vacia, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_ot_vacia = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_ot_vacia);
$texto_ot_vacia = preg_replace('/\s+/u', ' ', $texto_ot_vacia);
$texto_ot_vacia = trim($texto_ot_vacia);

$texto_bp_vacia = html_entity_decode($tabla_bp_vacia, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_bp_vacia = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_bp_vacia);
$texto_bp_vacia = preg_replace('/\s+/u', ' ', $texto_bp_vacia);
$texto_bp_vacia = trim($texto_bp_vacia);
	// Procesar datos del formulario (suponiendo que ya vienen en la codificación original)
$texto_dh_datos = strip_tags($datos['dist_horaria_planif']);
$texto_dh_datos = html_entity_decode($texto_dh_datos, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_dh_datos = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_dh_datos);
$texto_dh_datos = preg_replace('/\s+/u', ' ', $texto_dh_datos);
$texto_dh_datos = trim($texto_dh_datos);

$texto_ec_datos = strip_tags($datos['equipo_catedra_planif']);
$texto_ec_datos = html_entity_decode($texto_ec_datos, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_ec_datos = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_ec_datos);
$texto_ec_datos = preg_replace('/\s+/u', ' ', $texto_ec_datos);
$texto_ec_datos = trim($texto_ec_datos);

$texto_hc_datos = strip_tags($datos['horarios_consulta']);
$texto_hc_datos = html_entity_decode($texto_hc_datos, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_hc_datos = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_hc_datos);
$texto_hc_datos = preg_replace('/\s+/u', ' ', $texto_hc_datos);
$texto_hc_datos = trim($texto_hc_datos);

$texto_ot_datos = strip_tags($datos['otras_tareas']);
$texto_ot_datos = html_entity_decode($texto_ot_datos, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_ot_datos = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_ot_datos);
$texto_ot_datos = preg_replace('/\s+/u', ' ', $texto_ot_datos);
$texto_ot_datos = trim($texto_ot_datos);

$texto_bp_datos = strip_tags($datos['bibliografia_pedida']);
$texto_bp_datos = html_entity_decode($texto_bp_datos, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texto_bp_datos = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $texto_bp_datos);
$texto_bp_datos = preg_replace('/\s+/u', ' ', $texto_bp_datos);
$texto_bp_datos = trim($texto_bp_datos);
	
	// Comparación para dist_horaria_planif
if ($texto_dh_vacia == $texto_dh_datos) {
	$datos['dist_horaria_planif'] = null;
}
	// Comparación para equipo_catedra_planif
if ($texto_ec_vacia == $texto_ec_datos) {
	$datos['equipo_catedra_planif'] = null;
}
	// Comparación para horarios_consulta
if ($texto_hc_vacia == $texto_hc_datos) {
	$datos['horarios_consulta'] = null;
}
	// Comparación para otras_tareas
if ($texto_ot_vacia == $texto_ot_datos) {
	$datos['otras_tareas'] = null;
}
	// Comparación para bibliografia_pedida
if ($texto_bp_vacia == $texto_bp_datos) {
	$datos['bibliografia_pedida'] = null;
}

	//si borraron la tabla pone null

	$campos_a_null = array(
	'dist_horaria_planif',
	'equipo_catedra_planif',
	'horarios_consulta',
	'otras_tareas',
	'bibliografia_pedida'
		);
	foreach ($campos_a_null as $campo) {
		if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
			$datos[$campo] = null;
		}
		}
	
	// Manejo de comentarios
	$comentario = isset($datos['comentario']) ? $datos['comentario'] : '';
	if (!empty($comentario)) {
		// Obtener el timestamp y el nombre del usuario
		$timestamp = date('Y-m-d H:i:s');
		$usuario_id = toba::usuario()->get_id();
		$nombre_completo = toba::usuario()->get_nombre();
		
		// Crear el HTML del comentario
		$nuevo_comentario = "<div style='border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
								<strong>$nombre_completo ($usuario_id) - $timestamp</strong><br>
								<p style='margin: 5px 0;'>$comentario</p>
								</div>";

		// Obtener el contenido existente del campo comentarios
		$comentarios_planif = $this->dep('datos')->tabla('planificaciones')->get_columna('comentarios_planif');

		// Concatenar el nuevo comentario al contenido existente
		$nuevo_comentarios_planif = $comentarios_planif . $nuevo_comentario;

		// Asignar el nuevo valor a la columna comentarios_planif
		$datos['comentarios_planif'] = $nuevo_comentarios_planif;
	}
	
	// Control de campos obligatorios si cambia a depto
	$estado_planificacion = isset($datos['estado_planificacion']) ? $datos['estado_planificacion'] : "";
	if ($estado_planificacion == "depto" && ($datos['dist_horaria_planif']==null||$datos['horarios_consulta']==null||$datos['otras_tareas']==null))
		{
			$datos['estado_planificacion'] = 'docente';
			toba::notificacion()->info("FALTA COMPLETAR CAMPOS OBLIGATORIOS");
			toba::notificacion()->info("Los datos se guardaron pero continúa en estado BORRADOR.");
		}
	// Guardar los datos modificados
	$this->dep('datos')->tabla('planificaciones')->set($datos);
}


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

	
	
	function evt__guardar()
	{
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}
	
	
}
?>