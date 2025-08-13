<?php
class ci_depto_planificaciones extends catedras_ci
{
	protected $s__datos_filtro;
	
	private $id_planificacion_seleccionada;

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
		$perfil_usuario = toba::usuario()->get_perfiles_funcionales();

		// Lista de usuarios que no requieren el filtro
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_estado_depto();
		} else {
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_filtrado_depto($usuario_id, $perfil_usuario);
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
		$perfil_usuario = toba::usuario()->get_perfiles_funcionales();
		toba::logger()->info("Perfiles funcionales del usuario ($usuario_id): " . implode(', ', $perfiles_funcionales));

		// Lista de usuarios que no requieren el filtro
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_estado_aprobado();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('planificaciones')->get_listado_enviados_depto($usuario_id, $perfil_usuario);
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

	
		function evt__enviados__seleccion($datos)
	{
		$this->id_planificacion_seleccionada = $datos['id_planificacion']; // Guardar el ID de la planif seleccionada
		toba::memoria()->set_dato_operacion('id_planificacion_seleccionada', $this->id_planificacion_seleccionada); // Almacenar en memoria
		
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_ver');
		}
	

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('planificaciones')->get_datos_planificacion($this->id_planificacion_seleccionada));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}

		toba::notificacion()->info("En este formulario usted puede comentar, enviar a SAC o devolver al Docente responsable.");
	
	}

	function evt__formulario__modificacion($datos)
	{
		
		// Manejo de comentarios
		$estado_planificacion= isset($datos['estado_planificacion']) ? $datos['estado_planificacion'] : "";

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

		$this->dep('datos')->tabla('planificaciones')->set($datos);

// trae datos para registrar el movimiento 
	$usuario_id = toba::usuario()->get_id();
	$id_planif = isset($datos['id_planificacion']) ? $datos['id_planificacion'] : '';
	$est_mov = isset($datos['estado_planificacion']) ? $datos['estado_planificacion'] : '';
// Escapar valores
$nombre_tabla       = quote('planificaciones');
$id_tabla           = quote($id_planif);
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fecha_movimiento   = quote(date('Y-m-d H:i:s'));
$tipo_movimiento    = quote('Actualización');
$usuario_movimiento = quote($usuario_id);
$observaciones      = quote('Guardado desde formulario DEPTOS');
$estado_mov         = quote($est_mov);        

$sql = "
	INSERT INTO huayca.movimientos
	(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones, estado_mov)
	VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones, $estado_mov)
";

toba::db()->ejecutar($sql);
		

		
	}
//----------------- ver form planif
	function conf__ver_form_planif(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('planificaciones')->get_datos_planificacion($this->id_planificacion_seleccionada));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}

		toba::notificacion()->info("En esta sección usted puede VER las planificaciones enviadas a SAC.");
	
	}
	
	
//---------------------------------    
	

	
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