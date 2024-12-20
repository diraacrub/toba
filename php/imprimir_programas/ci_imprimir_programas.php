<?php
class ci_imprimir_programas extends catedras_ci
{
	protected $s__filtro;
	
	private $id_programa_seleccionado;

private $nombre_materia_selec;
private $cod_guarani_selec;
private $nombre_carrera_selec;
private $depto_selec;
private $area_selec;
private $orientacion_selec;
private $optativa_selec;
private $trayecto_selec;
private $periodo_dictado_selec;
private $ano_plan_selec;
private $horas_totales_selec;
private $horas_semanales_selec;
private $correlativas_para_cursar_selec;
private $correlativas_para_aprobar_selec;
private $contenidos_minimos_selec;
private $competencias_selec;
private $plan_ordenanzas_selec;
private $apellido_resp_selec;
private $nombre_resp_selec;
private $cargo_resp_selec;
private $equipo_catedra_selec;
private $fundamentacion_selec;
private $objetivos_selec;
private $programa_analitico_selec;
private $bibliografia_selec;
private $propuesta_metodologica_selec;
private $evaluacion_acreditacion_selec;
private $distribucion_horaria_selec;
private $horas_teoricas_selec;
private $horas_practicas_selec;
private $horas_teoricopracticas_selec;
private $cronograma_tentativo_selec;
private $estado_selec;
private $comentarios_selec;
private $comentario_selec;
private $cod_carrera_selec;
private $id_programa_selec;
	
	//-------- FILTRO ----

	function evt__filtro_programas__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function conf__filtro_programas($filtro)
	{
		if (isset($this->s__filtro)) {
			$filtro->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_programas__cancelar()
	{
		unset($this->s__filtro);    
	}
	
	//---- Cuadro -----------------------------------------------------------------------

	
	function conf__cuadro(toba_ei_cuadro $cuadro)
{
	if (isset($this->s__filtro)) {
		toba::logger()->info('Filtro aplicado: ' . json_encode($this->s__filtro));
		$datos = $this->dep('datos')->tabla('programas')->get_listado_para_imprimir_publico($this->s__filtro);
	} else {
		toba::logger()->info('Sin filtro');
		$datos = $this->dep('datos')->tabla('programas')->get_listado_para_imprimir_publico();
	}
	$cuadro->set_datos($datos);
}

	
	
	function evt__cuadro__seleccion($datos)
	{
		$this->id_programa_seleccionado = $datos['id_programa']; // Guardar el ID del programa seleccionado
		toba::memoria()->set_dato_operacion('id_programa_seleccionado', $this->id_programa_seleccionado); // Almacenar en memoria
				
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
{
	// Verificar si los datos están cargados
	if ($this->dep('datos')->esta_cargada()) {
		// Obtener los datos del programa
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Comprobar si los datos del programa fueron obtenidos
		if ($datos_programa) {
			
// Guardar los datos del programa
$this->ano_academico_selec = $datos_programa['ano_academico'];
$this->nombre_materia_selec = $datos_programa['nombre_materia'];
$this->cod_guarani_selec = $datos_programa['cod_guarani'];
$this->nombre_carrera_selec = $datos_programa['nombre_carrera'];
$this->depto_selec = $datos_programa['depto'];
$this->area_selec = $datos_programa['area'];
$this->orientacion_selec = $datos_programa['orientacion'];
$this->optativa_selec = $datos_programa['optativa'];
$this->trayecto_selec = $datos_programa['trayecto'];
$this->periodo_dictado_selec = $datos_programa['periodo_dictado'];
$this->ano_plan_selec = $datos_programa['ano_plan'];
$this->horas_totales_selec = $datos_programa['horas_totales'];
$this->horas_semanales_selec = $datos_programa['horas_semanales'];
$this->correlativas_para_cursar_selec = $datos_programa['correlativas_para_cursar'];
$this->correlativas_para_aprobar_selec = $datos_programa['correlativas_para_aprobar'];
$this->contenidos_minimos_selec = $datos_programa['contenidos_minimos'];
$this->competencias_selec = $datos_programa['competencias'];
$this->plan_ordenanzas_selec = $datos_programa['plan_ordenanzas'];
$this->apellido_resp_selec = $datos_programa['apellido_resp'];
$this->nombre_resp_selec = $datos_programa['nombre_resp'];
$this->cargo_resp_selec = $datos_programa['cargo_resp'];
$this->equipo_catedra_selec = $datos_programa['equipo_catedra'];
$this->fundamentacion_selec = $datos_programa['fundamentacion'];
$this->objetivos_selec = $datos_programa['objetivos'];
$this->programa_analitico_selec = $datos_programa['programa_analitico'];
$this->bibliografia_selec = $datos_programa['bibliografia'];
$this->propuesta_metodologica_selec = $datos_programa['propuesta_metodologica'];
$this->evaluacion_acreditacion_selec = $datos_programa['evaluacion_acreditacion'];
$this->distribucion_horaria_selec = $datos_programa['distribucion_horaria'];
$this->horas_teoricas_selec = $datos_programa['horas_teoricas'];
$this->horas_practicas_selec = $datos_programa['horas_practicas'];
$this->horas_teoricopracticas_selec = $datos_programa['horas_teoricopracticas'];
$this->cronograma_tentativo_selec = $datos_programa['cronograma_tentativo'];
$this->estado_selec = $datos_programa['estado'];
$this->comentarios_selec = $datos_programa['comentarios'];
//$this->comentario_selec = $datos_programa['comentario'];
$this->cod_carrera_selec = $datos_programa['cod_carrera'];
$this->id_programa_selec = $datos_programa['id_programa'];
// Almacenar en memoria
toba::memoria()->set_dato_operacion('ano_academico_selec', $this->ano_academico_selec); 
toba::memoria()->set_dato_operacion('nombre_materia_selec', $this->nombre_materia_selec); 
toba::memoria()->set_dato_operacion('cod_guarani_selec', $this->cod_guarani_selec); 
toba::memoria()->set_dato_operacion('nombre_carrera_selec', $this->nombre_carrera_selec); 
toba::memoria()->set_dato_operacion('depto_selec', $this->depto_selec); 
toba::memoria()->set_dato_operacion('area_selec', $this->area_selec); 
toba::memoria()->set_dato_operacion('orientacion_selec', $this->orientacion_selec); 
toba::memoria()->set_dato_operacion('optativa_selec', $this->optativa_selec); 
toba::memoria()->set_dato_operacion('trayecto_selec', $this->trayecto_selec); 
toba::memoria()->set_dato_operacion('periodo_dictado_selec', $this->periodo_dictado_selec); 
toba::memoria()->set_dato_operacion('ano_plan_selec', $this->ano_plan_selec); 
toba::memoria()->set_dato_operacion('horas_totales_selec', $this->horas_totales_selec); 
toba::memoria()->set_dato_operacion('horas_semanales_selec', $this->horas_semanales_selec); 
toba::memoria()->set_dato_operacion('correlativas_para_cursar_selec', $this->correlativas_para_cursar_selec); 
toba::memoria()->set_dato_operacion('correlativas_para_aprobar_selec', $this->correlativas_para_aprobar_selec); 
toba::memoria()->set_dato_operacion('contenidos_minimos_selec', $this->contenidos_minimos_selec); 
toba::memoria()->set_dato_operacion('competencias_selec', $this->competencias_selec); 
toba::memoria()->set_dato_operacion('plan_ordenanzas_selec', $this->plan_ordenanzas_selec); 
toba::memoria()->set_dato_operacion('apellido_resp_selec', $this->apellido_resp_selec); 
toba::memoria()->set_dato_operacion('nombre_resp_selec', $this->nombre_resp_selec); 
toba::memoria()->set_dato_operacion('cargo_resp_selec', $this->cargo_resp_selec); 
toba::memoria()->set_dato_operacion('equipo_catedra_selec', $this->equipo_catedra_selec); 
toba::memoria()->set_dato_operacion('fundamentacion_selec', $this->fundamentacion_selec); 
toba::memoria()->set_dato_operacion('objetivos_selec', $this->objetivos_selec); 
toba::memoria()->set_dato_operacion('programa_analitico_selec', $this->programa_analitico_selec); 
toba::memoria()->set_dato_operacion('bibliografia_selec', $this->bibliografia_selec); 
toba::memoria()->set_dato_operacion('propuesta_metodologica_selec', $this->propuesta_metodologica_selec); 
toba::memoria()->set_dato_operacion('evaluacion_acreditacion_selec', $this->evaluacion_acreditacion_selec); 
toba::memoria()->set_dato_operacion('distribucion_horaria_selec', $this->distribucion_horaria_selec); 
toba::memoria()->set_dato_operacion('horas_teoricas_selec', $this->horas_teoricas_selec); 
toba::memoria()->set_dato_operacion('horas_practicas_selec', $this->horas_practicas_selec); 
toba::memoria()->set_dato_operacion('horas_teoricopracticas_selec', $this->horas_teoricopracticas_selec); 
toba::memoria()->set_dato_operacion('cronograma_tentativo_selec', $this->cronograma_tentativo_selec); 
toba::memoria()->set_dato_operacion('estado_selec', $this->estado_selec); 
toba::memoria()->set_dato_operacion('comentarios_selec', $this->comentarios_selec); 
toba::memoria()->set_dato_operacion('comentario_selec', $this->comentario_selec); 
toba::memoria()->set_dato_operacion('cod_carrera_selec', $this->cod_carrera_selec); 
toba::memoria()->set_dato_operacion('id_programa_selec', $this->id_programa_selec); 
			
			
			// Establecer los datos en el formulario
			$form->set_datos($datos_programa);

			// Setear de solo lectura para 'contenidos_minimos'
			$form->set_solo_lectura(array('contenidos_minimos'), true);

			// Definir carreras que no muestran horas teóricas prácticas
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			// Verificar si la carrera pertenece a las que están en el array
			if (in_array($datos_programa['cod_carrera'], $carreras_coneau)) {
				// Hacer el campo "horas_teoricopracticas" solo lectura
				$form->set_solo_lectura(array('horas_teoricopracticas'), true);
			} else {
				// Asegurarse de que el campo sea editable
				$form->set_solo_lectura(array('horas_teoricopracticas'), false);
			}
		}
	} else {
		// Si no hay datos cargados, eliminar el evento de eliminar
		$this->pantalla()->eliminar_evento('eliminar');
	}
}





/////////////////////////



	function conf__formulario_viejo(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('programas')->get());
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	function evt__formulario__modificacion($datos)
	{
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