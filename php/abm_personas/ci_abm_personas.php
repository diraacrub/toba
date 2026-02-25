<?php
class ci_abm_personas extends catedras_ci
{
	protected $s__tiene_hijos;
	protected $s__datos_filtro;
	protected $s__hijos = array();



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
			$cuadro->set_datos($this->dep('datos')->tabla('personas')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('personas')->get_listado());
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
			$form->set_datos($this->dep('datos')->tabla('personas')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}
	
	
	//---- Formulario ML Hijos -----------------------------------------------------------
	
	function conf__hijos(toba_ei_formulario_ml $form)
{
		// &#128313; ALTA &#8594; iniciar colapsado
	if (!$this->dep('datos')->esta_cargada()) {
		$form->colapsar(true);
		return;
	}
	// &#128313; EDICIÓN &#8594; cargar hijos si existen
	$persona = $this->dep('datos')->tabla('personas')->get();

	if (!empty($persona['hijos']) && $persona['hijos'] !== '[]') {
		$datos = json_decode($persona['hijos'], true);

		if (!empty($datos)) {
	// Bloquear que sea colapsable
	$form->set_colapsable(false);
			$form->set_datos($datos);
			// &#10071; NO colapsar &#8594; dejar que Toba lo muestre abierto
		}
	} else {
		// sin hijos &#8594; iniciar colapsado
		$form->colapsar(false);
	}
}   
	
	
	
	
	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('personas')->set($datos);
	}
	

	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	
	
	

	function evt__hijos__modificacion($datos)
{
	$this->s__hijos = $datos;
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
	$hijos_limpio = array();

	foreach ($this->s__hijos as $fila) {
		if (trim($fila['nombre']) != '') {

			// &#128313; Normalizar a UTF-8 SOLO si hiciera falta
			foreach ($fila as $k => $v) {
				if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) {
					$fila[$k] = utf8_encode($v);
				}
			}

			$hijos_limpio[] = $fila;
		}
	}

	// &#128313; JSON sin escapar unicode
	$json = json_encode($hijos_limpio, JSON_UNESCAPED_UNICODE);

	if ($json === false) {
		toba::notificacion()->error('Error JSON: ' . json_last_error_msg());
		return;
	}

	// &#128313; Guardar sin tocar el resto de la fila
	$this->dep('datos')->tabla('personas')->set(array(
		'hijos' => $json
	));

	$this->dep('datos')->sincronizar();
	$this->resetear();
}
	
	
	function evt__guardar5()
{
	$hijos_limpio = array();

	foreach ($this->s__hijos as $fila) {
		if (trim($fila['nombre']) != '') {
			$hijos_limpio[] = $fila;
		}
	}

	$json = json_encode($hijos_limpio);

	if ($json === false) {
		toba::notificacion()->error('Error JSON: ' . json_last_error_msg());
		return;
	}

	$this->dep('datos')->tabla('personas')->set(
		array('hijos' => $json)
	);

	$this->dep('datos')->sincronizar();
	$this->resetear();
}
	
	
	
	function evt__guardar4()
{
	// &#128313; limpiar filas vacías
	$hijos_limpio = array();

	
	foreach ($this->s__hijos as $fila) {
	if (trim($fila['nombre']) != '') {

		foreach ($fila as $k => $v) {
			if (is_string($v)) {

				// &#128269; Detectar si NO es UTF-8
				if (!mb_check_encoding($v, 'UTF-8')) {
					$fila[$k] = iconv('ISO-8859-1', 'UTF-8//IGNORE', $v);
				}

			}
		}

		$hijos_limpio[] = $fila;
	}
}
	
	
	
	
	
	
	// &#128308; DEBUG: ver qué llega desde el ML
	toba::notificacion()->info(print_r($hijos_limpio, true));

	// &#128308; PROBAR JSON
	$json = json_encode($hijos_limpio, JSON_UNESCAPED_UNICODE);

	if ($json === false) {
		toba::notificacion()->error('Error JSON: ' . json_last_error_msg());
		return;
	}

	// &#128313; guardar
	$this->dep('datos')->tabla('personas')->set(
		array('hijos' => $json)
	);

	$this->dep('datos')->sincronizar();
	$this->resetear();
}
	
	
	function evt__guardar3()
{
	toba::notificacion()->info(print_r($this->s__hijos, true));

	$hijos_limpio = array();

	foreach ($this->s__hijos as $fila) {
		if (trim($fila['nombre']) != '') {
			$hijos_limpio[] = $fila;
		}
	}

	$json = json_encode($hijos_limpio, JSON_UNESCAPED_UNICODE);

	// &#128313; obtener fila actual
	$fila = $this->dep('datos')->tabla('personas')->get();

	// &#128313; asignar hijos
	$fila['hijos'] = $json;

	// &#128313; guardar fila completa
	$this->dep('datos')->tabla('personas')->set($fila);

	$this->dep('datos')->sincronizar();
	$this->resetear();
}
	
	
	
	function evt__guardar_2()
{
	
	toba::notificacion()->info(print_r($this->s__hijos, true));
	
	$hijos_limpio = array();

	foreach ($this->s__hijos as $fila) {
		if (trim($fila['nombre']) != '') {
			$hijos_limpio[] = $fila;   // &#8592; sin tocar encoding
		}
	}

	// JSON limpio
	$json = json_encode($hijos_limpio, JSON_UNESCAPED_UNICODE);

	$this->dep('datos')->tabla('personas')->set(
		array('hijos' => $json)
	);

	$this->dep('datos')->sincronizar();
	$this->resetear();
}    
	
	
	
	function evt__guardar_1()
{
	// &#128313; limpiar filas vacías
	$hijos_limpio = array();

	foreach ($this->s__hijos as $fila) {
		if (trim($fila['nombre']) != '') {

			// acá
			// &#128313; normalizar encoding a UTF-8
			foreach ($fila as $k => $v) {
				if (is_string($v)) {
					$fila[$k] = mb_convert_encoding($v, 'UTF-8', 'auto');
				}
			}

			
			
			$hijos_limpio[] = $fila;
		}
	}

	$json = json_encode($hijos_limpio);

	if ($json === false) {
		toba::notificacion()->error('Error JSON: ' . json_last_error_msg());
		return;
	}

	// &#128313; guardar
	$this->dep('datos')->tabla('personas')->set(
		array('hijos' => $json)
	);

	$this->dep('datos')->sincronizar();
	$this->resetear();
}   
	

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

		function evt__cuadro__ver($seleccion)
{
	$this->dep('datos')->cargar($seleccion);  // &#128293; FALTABA ESTO
	$this->set_pantalla('pant_ver');
}
	
	//-----------------------------------------------------------------------------------
	//---- ver_form_persona -------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	
	
	
	
	
	
	function conf__ver_form_persona(catedras_ei_formulario $form)
{
	if ($this->dep('datos')->esta_cargada()) {

		$datos = $this->dep('datos')->tabla('personas')->get();
		
		$datos['hijos_json'] = $datos['hijos'];

		// &#128313; transformar JSON &#8594; tabla HTML
		$datos['hijos'] = $this->generar_tabla_hijos($datos['hijos'] ?? null);

		$form->set_datos($datos);

	} else {
		$this->pantalla()->eliminar_evento('eliminar');
	}
}
	
	
	protected function generar_tabla_hijos($json)
{
	mb_internal_encoding("UTF-8");
	
	if (empty($json) || $json === '[]') {
		return '<b>Sin hijos</b>';
	}

	$datos = json_decode($json, true);

	if (empty($datos)) {
		return '<b>Sin hijos</b>';
	}

	$html = '<table border="1" cellpadding="4" cellspacing="0" style="font-family: sans-serif;">';
	$html .= '<tr>
				<th>Nombre</th>
				<th>Apellido</th>
				<th>Fecha Nac.</th>
				<th>Género</th>
				</tr>';

	foreach ($datos as $fila) {
		$nombre   = htmlspecialchars($fila['nombre'] ?? '');
		$apellido = htmlspecialchars($fila['apellido'] ?? '');
		$fec_nac  = htmlspecialchars($fila['fec_nac'] ?? '');
		$genero   = htmlspecialchars($fila['genero'] ?? '');

		$html .= "<tr>
					<td>{$nombre}</td>
					<td>{$apellido}</td>
					<td>{$fec_nac}</td>
					<td>{$genero}</td>
					</tr>";
	}

	$html .= '</table>';

	return $html;
}
	
}
?>