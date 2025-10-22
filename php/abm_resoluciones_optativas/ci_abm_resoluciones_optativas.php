<?php
require_once 'ci_base_operaciones.php';
class ci_abm_resoluciones_optativas extends ci_base_operaciones
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
		$datos = $this->dep('datos')->tabla('res_optativas')->get_listado($this->s__datos_filtro);
	} else {
		$datos = $this->dep('datos')->tabla('res_optativas')->get_listado();
	}

	// Recorro los resultados y reemplazo el valor del campo 'enlace' por un <a>
	foreach ($datos as $key => $fila) {
		if (!empty($fila['enlace'])) {
			$url = htmlspecialchars($fila['enlace'], ENT_QUOTES);
			$datos[$key]['enlace'] = "<a href=\"$url\" target=\"_blank\">Ver Resolución</a>";
		}
	}

	$cuadro->set_datos($datos);
}

	
	
	
	
	function conf__cuadro_borrar(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('res_optativas')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('res_optativas')->get_listado());
		}
	}

	function evt__cuadro__eliminar($datos)
	{
		$this->dep('datos')->resetear();
		$this->dep('datos')->cargar($datos);
		$this->dep('datos')->eliminar_todo();
		$this->dep('datos')->resetear();
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
			$form->set_datos($this->dep('datos')->tabla('res_optativas')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('res_optativas')->set($datos);
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