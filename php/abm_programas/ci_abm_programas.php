<?php
require_once 'ci_base_operaciones.php';

class ci_abm_programas extends ci_base_operaciones
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
			$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_abm_programas_filtrado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_abm_programas_filtrado());
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
			$form->set_datos($this->dep('datos')->tabla('programas')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	function evt__formulario__modificacion($datos)
	{
		// Guardar los datos viejos antes de setear los nuevos
		if ($this->dep('datos')->tabla('programas')->esta_cargada()) {
			$this->s__datos_viejos = $this->dep('datos')->tabla('programas')->get();
		}
		$this->dep('datos')->tabla('programas')->set($datos);
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
		// ---- Datos del programa antes de eliminar ----
		$programa = $this->dep('datos')->tabla('programas')->get();
		$id_prog = isset($programa['id_programa']) ? $programa['id_programa'] : null;

		// ---- Datos para el log ----
		$usuario_id = toba::usuario()->get_id();
		$nombre_tabla = quote('programas');
		$id_tabla = quote($id_prog);
		date_default_timezone_set('America/Argentina/Buenos_Aires');
		$fecha_movimiento = quote(date('Y-m-d H:i:s'));
		$tipo_movimiento = quote('Eliminación');
		$usuario_movimiento = quote($usuario_id);

		// ---- Armo observaciones ----
		$observaciones_txt = $this->armar_observaciones($programa, "Eliminado desde abm Programas");
		$observaciones = quote($observaciones_txt);

		// ---- Insert en movimientos ----
		$sql = "
			INSERT INTO huayca.movimientos
			(nombre_tabla, id_tabla, fecha_movimiento, tipo_movimiento, usuario_movimiento, observaciones)
			VALUES ($nombre_tabla, $id_tabla, $fecha_movimiento, $tipo_movimiento, $usuario_movimiento, $observaciones)
		";
		toba::db()->ejecutar($sql);

		// ---- Elimina el programa ----
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__guardar()
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_tabla = quote('programas');
		date_default_timezone_set('America/Argentina/Buenos_Aires');
		$fecha_movimiento = quote(date('Y-m-d H:i:s'));
		$usuario_movimiento = quote($usuario_id);

		// ID del programa
		$programa = $this->dep('datos')->tabla('programas')->get();
		$id_prog = isset($programa['id_programa']) ? $programa['id_programa'] : null;    
		$id_tabla = quote($id_prog);

		if ($this->dep('datos')->tabla('programas')->esta_cargada()) {
			// ---- Actualización ----
			$observaciones_txt = $this->armar_observaciones($this->s__datos_viejos, "Actualización desde abm Programas");
			$tipo_movimiento = quote('Actualización');
		} else {
			// ---- Alta ----
			$observaciones_txt = $this->armar_observaciones($programa, "Alta desde abm Programas");
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

		// Guardar finalmente el programa
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}


	// Función auxiliar para armar observaciones
	private function armar_observaciones($datos_programa, $accion)
{
	// Campos de programa que sí queremos guardar
	$incluir = array(
		'id_programa',
		'id_materia_prog',
		'estado',
		'ano_academico',
		'periodo_dictado',
		'nombre_resp',
		'apellido_resp'
	);
	$txt = "";

	foreach ($datos_programa as $campo => $valor) {
		if (in_array($campo, $incluir)) {

			// Si es id_materia_prog, agregamos info de la materia relacionada
			if ($campo == 'id_materia_prog' && !empty($valor)) {
				$materia = toba::db('catedras')->consultar("
					SELECT m.nombre_materia, m.cod_guarani, m.cod_carrera, m.plan_ordenanzas
					FROM materias m
					WHERE m.id_materia = " . quote($valor) . "
				");

				if (!empty($materia)) {
					$info_materia = "  nombre_materia: <strong>" . $materia[0]['nombre_materia'] . "</strong><br>"
						. "  cod_guarani: <strong>" . $materia[0]['cod_guarani'] . "</strong><br>"
						. "  cod_carrera: <strong>" . $materia[0]['cod_carrera'] . "</strong><br>"
						. "  plan_ordenanzas: <strong>" . $materia[0]['plan_ordenanzas'] . "</strong>";
					$valor .= "<br>" . $info_materia;
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