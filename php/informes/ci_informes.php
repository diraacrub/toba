<?php
require_once 'ci_base_operaciones.php';
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

	
	function conf__formulario(toba_ei_formulario $form)
	{
		
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('informes')->get_datos_informe($this->id_informe_seleccionado));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}

		toba::notificacion()->info("Completar sin alterar la estructura del cuadro en el punto 11.Gracias");
	
	}
	
	
	
	function evt__formulario__modificacion($datos)
	{
	
		// Guardar datos viejos antes de modificar
	
	if ($this->dep('datos')->tabla('informes')->esta_cargada()) {
		$this->s__datos_viejos = $this->dep('datos')->tabla('informes')->get();
		} else {
		$this->s__datos_viejos = $datos;
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
		$comentarios_inf = $this->dep('datos')->tabla('informes')->get_columna('comentarios_inf');

		// Concatenar el nuevo comentario al contenido existente
		$nuevo_comentarios_inf = $comentarios_inf . $nuevo_comentario;

		// Asignar el nuevo valor a la columna comentarios_inf
		$datos['comentarios_inf'] = $nuevo_comentarios_inf;
	}
	
	//***PEGUÉ DESDE ACÁ
		
		
	// Validación de cantidad de estudiantes campos obligatorios

		
$inscriptos   = isset($datos['inscriptos']) ? $datos['inscriptos'] : 0;
$comenzaron   = isset($datos['comenzaron']) ? $datos['comenzaron'] : 0;
$aprobaron    = isset($datos['aprobaron']) ? $datos['aprobaron'] : 0;
$desaprobaron = isset($datos['desaprobaron']) ? $datos['desaprobaron'] : 0;
$abandonaron  = isset($datos['abandonaron']) ? $datos['abandonaron'] : 0;

$suma_resultados = $aprobaron + $desaprobaron + $abandonaron;

// Validaciones
if ($comenzaron > $inscriptos) {
	throw new toba_error("El número de estudiantes que comenzaron no puede ser mayor que los inscriptos.");
	toba::notificacion()->info(" "); 
}

if ($suma_resultados != $comenzaron) {
	throw new toba_error("La suma de aprobaron, desaprobaron y abandonaron debe ser igual a los que comenzaron.");
	toba::notificacion()->info(" ");
}
		
		
		
		
		
	// Validación de campos obligatorios
	$campos_obligatorios = array('inscriptos','causas_abandono_desap','caract_grupo',
									'estrategias','consideraciones_interior','analisis_actividades',
									'suficiencia_adecuacion','evaluacion_ays','articulacion','capacitacion',
									'analisis_por_cargo');

	foreach ($campos_obligatorios as $campo) {
		if ($datos['estado_informe'] === 'depto' && empty($datos[$campo])) {
			$datos['estado_informe'] = 'docente';
			$datos['firma_doc_inf'] = '';
			$this->dep('datos')->tabla('informes')->set($datos);
			$this->dep('datos')->sincronizar();
			throw new toba_error("Falta cargar datos obligatorios, por favor corrija.");
			toba::notificacion()->info(" ");
		}
	}

	// Guardar finalmente los datos en programas
	$this->dep('datos')->tabla('informes')->set($datos);
	$this->dep('datos')->tabla('informes')->sincronizar();
	toba::notificacion()->agregar("Su informe ha sido guardado correctamente", 'info');


		
	//***HASTA ACÁ    

	// ---- REGISTRO DE MOVIMIENTOS ----
	$est_mov = isset($datos['estado_informe']) ? $datos['estado_informe'] : "";
	$estado_mov = quote($est_mov);  
	$usuario_id = toba::usuario()->get_id();
	$nombre_tabla = quote('informes');
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	$fecha_movimiento = quote(date('Y-m-d H:i:s'));
	$usuario_movimiento = quote($usuario_id);

	// ID de la planificación
	$inf = $this->dep('datos')->tabla('informes')->get();
	$id_informe = isset($datos['id_informe']) ? $datos['id_informe'] : null;    
	$id_tabla = quote($id_informe);
	$observaciones_txt = $this->armar_observaciones_inf($this->s__datos_viejos, "Actualización desde formulario");
	$tipo_movimiento = quote('Actualización');
	$observaciones = quote($observaciones_txt);
	// Insert en movimientos
	$sql = "
		INSERT INTO huayca.movimientos
		(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones,estado_mov)
		VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones, $estado_mov)
	";
	toba::db()->ejecutar($sql);
	//----

	// Guardar los datos modificados
	$this->dep('datos')->tabla('informes')->set($datos);
	// $this->resetear();
	
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

	
	
	function evt__guardar()
	{
		$this->dep('datos')->sincronizar();
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