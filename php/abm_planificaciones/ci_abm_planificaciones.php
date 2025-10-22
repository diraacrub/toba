<?php
require_once 'ci_base_operaciones.php';
class ci_abm_planificaciones extends ci_base_operaciones
{
	protected $s__datos_filtro;
	protected $s__datos_viejos; // Para guardar los datos antes de modificar    


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
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('planificaciones')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('planificaciones')->get_listado());
		}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('planificaciones')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	
	function evt__formulario__modificacion($datos)
	{
if ($datos['dist_horaria_planif'] == '') {
	$datos['dist_horaria_planif'] = null;}
if ($datos['equipo_catedra_planif'] == '') {
	$datos['equipo_catedra_planif'] = null;}
if ($datos['horarios_consulta'] == '') {
	$datos['horarios_consulta'] = null;}
if ($datos['otras_tareas'] == '') {
	$datos['otras_tareas'] = null;}
if ($datos['bibliografia_pedida'] == '') {
	$datos['bibliografia_pedida'] = null;}

	// Guardar los datos viejos antes de setear los nuevos
		if ($this->dep('datos')->tabla('planificaciones')->esta_cargada()) {
			$this->s__datos_viejos = $this->dep('datos')->tabla('planificaciones')->get();
		}        
	// *****        
		
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
	// ---- Datos de la planificación antes de eliminar ----
	$planif = $this->dep('datos')->tabla('planificaciones')->get();
	$id_planificacion = isset($planif['id_planificacion']) ? $planif['id_planificacion'] : null;

	// ---- Datos para el log ----
	$usuario_id = toba::usuario()->get_id();
	$nombre_tabla = quote('planificaciones');
	$id_tabla = quote($id_planificacion);
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	$fecha_movimiento = quote(date('Y-m-d H:i:s'));
	$tipo_movimiento = quote('Eliminación');
	$usuario_movimiento = quote($usuario_id);

	// ---- Armo observaciones ----
	$observaciones_txt = $this->armar_observaciones_planif($planif, "Eliminado desde abm Planificaciones");
	$observaciones = quote($observaciones_txt);

	// ---- Insert en movimientos ----
	$sql = "
		INSERT INTO huayca.movimientos
		(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones)
		VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones)
	";
	toba::db()->ejecutar($sql);

	// ---- Elimina la planificación ----
	$this->dep('datos')->eliminar_todo();
	$this->resetear();
}
	
	
	
	
	function evt__eliminar_original()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	
function evt__guardar()
{
	$usuario_id = toba::usuario()->get_id();
	$nombre_tabla = quote('planificaciones');
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	$fecha_movimiento = quote(date('Y-m-d H:i:s'));
	$usuario_movimiento = quote($usuario_id);

	// ID de la planificación
	$planif = $this->dep('datos')->tabla('planificaciones')->get();
	$id_planificacion = isset($planif['id_planificacion']) ? $planif['id_planificacion'] : null;    
	$id_tabla = quote($id_planificacion);

	if ($this->dep('datos')->tabla('planificaciones')->esta_cargada()) {
		// ---- Actualización ----
		$observaciones_txt = $this->armar_observaciones_planif($this->s__datos_viejos, "Actualización desde abm Planificaciones");
		$tipo_movimiento = quote('Actualización');
	} else {
		// ---- Alta ----
		$observaciones_txt = $this->armar_observaciones_planif($planif, "Alta desde abm Planificaciones");
		$tipo_movimiento = quote('Alta');
	}

	$observaciones = quote($observaciones_txt);

	// Insert en movimientos
	$sql = "
		INSERT INTO huayca.movimientos
		(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones)
		VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones)
	";
	toba::db()->ejecutar($sql);

	// Guardar finalmente la planificación
	$this->dep('datos')->sincronizar();
	$this->resetear();
}
	
	
	
	
	
	function evt__guardar_original()
	{
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}
	
	
	// Función auxiliar para armar observaciones de planificaciones
// Función auxiliar para armar observaciones de planificaciones
private function armar_observaciones_planif($datos_planif, $accion)
{
	// Campos de planif que sí queremos guardar
	$incluir = array('id_planificacion', 'id_prog_planif', 'estado_planificacion');
	$txt = "";

	foreach ($datos_planif as $campo => $valor) {
		if (in_array($campo, $incluir)) {

			// Si es id_prog_planif, buscamos la materia relacionada
			if ($campo == 'id_prog_planif' && !empty($valor)) {
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