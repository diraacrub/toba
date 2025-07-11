<?php
class ci_abm_planificaciones extends catedras_ci
{
	protected $s__datos_filtro;


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