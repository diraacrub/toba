<?php
class ci_abm_materias extends catedras_ci
{
	protected $s__datos_filtro;
	protected $s__datos_viejos;



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
			$cuadro->set_datos($this->dep('datos')->tabla('materias')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('materias')->get_listado());
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
			$form->set_datos($this->dep('datos')->tabla('materias')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}
	
	
	function evt__formulario__modificacion($datos)
{
	// Capturamos los datos antiguos
	if ($this->dep('datos')->tabla('materias')->esta_cargada()) {
		$this->s__datos_viejos = $this->dep('datos')->tabla('materias')->get();
	}
	
	// Guardamos los datos nuevos en el objeto de datos
	$this->dep('datos')->tabla('materias')->set($datos);
}


	function evt__formulario__modificacion_borrar($datos)
	{
		$this->dep('datos')->tabla('materias')->set($datos);
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
	// ---- Datos de la materia antes de eliminar ----
	$materia = $this->dep('datos')->tabla('materias')->get();
	$id_mat = isset($materia['id_materia']) ? $materia['id_materia'] : null;

	// ---- Datos para el log ----
	$usuario_id = toba::usuario()->get_id();
	$nombre_tabla = quote('materias');
	$id_tabla = quote($id_mat);
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	$fecha_movimiento = quote(date('Y-m-d H:i:s'));
	$tipo_movimiento = quote('Eliminación');
	$usuario_movimiento = quote($usuario_id);

	// ---- Armo el texto de observaciones con todos los campos ----
	$observaciones_txt = $this->armar_observaciones($materia, "Eliminada desde abm Materias");
	$observaciones = quote($observaciones_txt);

	// ---- Insert en la tabla de movimientos ----
	$sql = "
		INSERT INTO huayca.movimientos
		(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones)
		VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones)
	";
	toba::db()->ejecutar($sql);

	// ---- Elimina la materia ----
	$this->dep('datos')->eliminar_todo();
	$this->resetear();
}


	function evt__guardar()
{
	$usuario_id = toba::usuario()->get_id();
	$nombre_tabla = quote('materias');
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	$fecha_movimiento = quote(date('Y-m-d H:i:s'));
	$usuario_movimiento = quote($usuario_id);

	$materia = $this->dep('datos')->tabla('materias')->get();
	$id_mat = isset($materia['id_materia']) ? $materia['id_materia'] : null;    
	$id_tabla = quote($id_mat);
	
	
	if ($this->dep('datos')->tabla('materias')->esta_cargada()) {
	// ---- Actualización ----
	$observaciones_txt = $this->armar_observaciones($this->s__datos_viejos, "Actualización desde abm Materias");
	$tipo_movimiento = quote('Actualización');
} else {
	// ---- Alta ----
	$materia = $this->dep('datos')->tabla('materias')->get();
	$observaciones_txt = $this->armar_observaciones($materia, "Alta desde abm Materias");
	$tipo_movimiento = quote('Alta');
}

	$observaciones = quote($observaciones_txt);

	$sql = "
		INSERT INTO huayca.movimientos
		(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones)
		VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones)
	";
	toba::db()->ejecutar($sql);

	// Guardar finalmente la materia
	$this->dep('datos')->sincronizar();
	$this->resetear();
}

	
// funcion para armar el campo observaciones para la tabla movimientos    

private function armar_observaciones($datos_materia, $accion)
{
	$excluir = array('x_dbr_clave'); // campos internos de Toba que no queremos loguear
	$txt = "";
	foreach ($datos_materia as $campo => $valor) {
		if (!in_array($campo, $excluir)) {
			if ($campo === 'nombre_materia') {
				$valor = "<strong>$valor</strong>";
			}
			$txt .= $campo . ": " . $valor . "<br>";
		}
	}
	$txt .= $accion;
	return $txt;
}

	
	
	
	


}
?>