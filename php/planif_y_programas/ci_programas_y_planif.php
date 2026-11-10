<?php
require_once 'ci_base_operaciones.php';
class ci_programas_y_planif extends ci_base_operaciones
{
	
	
	//esto es de operaciones docentes
	
private $id;
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
private $firma_doc_selec;
private $firma_dto_selec;
private $firma_sac_selec;

	
	
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	

	
	//---- CUADROS
	
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_docente();
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_filtrado($usuario_id);
		}
		
		foreach ($datos as $key => $registro) {
			if ($registro ['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';        
			}
			if ($registro ['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';        
			}
		}
			
		
		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}

	
	////-------------------
		
	
	function evt__cuadro__seleccion($datos)
{
	$this->id_programa_seleccionado = $datos['id_programa']; // Guardar el ID del programa seleccionado
	toba::memoria()->set_dato_operacion('id_programa_seleccionado', $this->id_programa_seleccionado); // Almacenar en memoria

	$this->dep('datos')->cargar($datos);
	$this->set_pantalla('pant_edicion');
}
		
	
	
	/// Cuadro enviados///
	
		function conf__enviados(catedras_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		toba::logger()->info("Perfiles funcionales del usuario ($usuario_id): " . implode(', ', $perfiles_funcionales));

		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		$excepciones = array('admin');
		if (array_intersect($perfiles_funcionales, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_depto_sac();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('programas')->get_listado_enviados($usuario_id);
		}
		foreach ($datos as $key => $registro) {
			if ($registro ['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';        
			}
			if ($registro ['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';        
			}
		}
			

		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}
	
	
	
		function evt__enviados__seleccion($datos)
{
	$this->id_programa_seleccionado = $datos['id_programa']; // Guardar el ID del programa seleccionado
	toba::memoria()->set_dato_operacion('id_programa_seleccionado', $this->id_programa_seleccionado); // Almacenar en memoria

	$this->dep('datos')->cargar($datos);
	$this->set_pantalla('pant_ver');
}

	// ************ SEGUNDA PANTALLA ************************
	
//-----------------------------------------------------------------------------------------------    
	
	

function resetear()
{
	// Todas las claves temporales utilizadas durante la edicion.
	$claves_temporales = array(
		// Datos pendientes de los formularios.
		'datos_formulario_con_todo_pendientes',
		'datos_2_formulario_con_todo_pendientes',

		// Datos pendientes de los multilinea.
		'datos_equipo_de_catedra_pendientes',
		'datos_dist_hor_planif_pendientes',
		'datos_horario_de_consulta_pendientes',
		'datos_otras_tareas_pendientes',
		'datos_bibliografia_pedida_pendientes',

		// Estado, firma y comentarios.
		'comentario_nuevo_pendiente',
		'estado_nuevo_pendiente',
		'estado_nuevo_a_aplicar',
		'estado_programa_al_cargar',
		'comentarios_a_aplicar',
		'firma_a_aplicar',

		// Identificacion del programa.
		'id_programa_seleccionado',

		// Datos del programa seleccionado.
		'ano_academico_selec',
		'nombre_materia_selec',
		'cod_guarani_selec',
		'nombre_carrera_selec',
		'depto_selec',
		'area_selec',
		'orientacion_selec',
		'optativa_selec',
		'trayecto_selec',
		'periodo_dictado_selec',
		'ano_plan_selec',
		'horas_totales_selec',
		'horas_semanales_selec',
		'correlativas_para_cursar_selec',
		'correlativas_para_aprobar_selec',
		'contenidos_minimos_selec',
		'competencias_selec',
		'plan_ordenanzas_selec',
		'apellido_resp_selec',
		'nombre_resp_selec',
		'cargo_resp_selec',
		'equipo_catedra_selec',
		'fundamentacion_selec',
		'objetivos_selec',
		'programa_analitico_selec',
		'bibliografia_selec',
		'propuesta_metodologica_selec',
		'evaluacion_acreditacion_selec',
		'distribucion_horaria_selec',
		'horas_teoricas_selec',
		'horas_practicas_selec',
		'horas_teoricopracticas_selec',
		'cronograma_tentativo_selec',
		'estado_selec',
		'comentarios_selec',
		'comentario_selec',
		'cod_carrera_selec',
		'id_programa_selec',
		'firma_doc_selec',
		'firma_dto_selec',
		'firma_sac_selec'
	);

	// Limpiar todas las claves de memoria de esta operacion.
	foreach ($claves_temporales as $clave) {
		toba::memoria()->set_dato_operacion($clave, null);
	}

	// Limpiar tambien la propiedad del CI.
	$this->id_programa_seleccionado = null;

	// Limpiar los datos cargados en los componentes de Toba.
	$this->dep('datos')->resetear();

	// Volver a la pantalla de seleccion.
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
		
	



	//--$datos_programa---------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__imprimir = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__guardar = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__prueba = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__prueba = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__volver = function()
		{
		}
		";
	}




	

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	
	
	// ======================================================
// AUXILIARES PARA LA PANTALLA DE EDICION
// ======================================================


// Recuperar el ID del programa seleccionado.
function edicion_obtener_id_programa()
{
	if (!isset($this->id_programa_seleccionado)
		|| $this->id_programa_seleccionado == '') {

		$this->id_programa_seleccionado =
			toba::memoria()->get_dato_operacion(
				'id_programa_seleccionado'
			);
	}

	if (!empty($this->id_programa_seleccionado)) {
		toba::memoria()->set_dato_operacion(
			'id_programa_seleccionado',
			$this->id_programa_seleccionado
		);
	}

	return $this->id_programa_seleccionado;
}


// Recuperar los datos del programa y, cuando se indica una clave,
// combinar los datos pendientes con los datos originales.
function edicion_obtener_datos_programa($clave_pendientes)
{
	$id_programa = $this->edicion_obtener_id_programa();

	if (empty($id_programa) || !$this->dep('datos')->esta_cargada()) {
		return false;
	}

	$datos = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos) {
		return false;
	}

	if (!empty($clave_pendientes)) {
		$datos_pendientes = toba::memoria()->get_dato_operacion(
			$clave_pendientes
		);

		if (is_array($datos_pendientes)) {
			$datos = array_merge(
				$datos,
				$datos_pendientes
			);
		}
	}

	return $datos;
}


// Cargar un formulario multilínea desde una columna JSON.
// Si existen datos pendientes, se muestran sin consultar la base.
function edicion_cargar_multilinea(
	$form,
	$columna,
	$campos,
	$clave_pendientes
)
{
	$datos_pendientes = toba::memoria()->get_dato_operacion(
		$clave_pendientes
	);

	if (is_array($datos_pendientes)) {
		$form->set_datos($datos_pendientes);
		return;
	}

	$datos_programa = $this->edicion_obtener_datos_programa(null);

	if (!$datos_programa || empty($datos_programa[$columna])) {
		return;
	}

	$json_utf8 = mb_convert_encoding(
		$datos_programa[$columna],
		'UTF-8',
		'ISO-8859-1'
	);

	$filas = json_decode($json_utf8, true);

	if (!is_array($filas)) {
		return;
	}

	$datos_ml = array();

	foreach ($filas as $fila) {
		$registro = array();

		foreach ($campos as $indice => $campo) {
			$valor = isset($fila[$indice]) ? $fila[$indice] : '';

			if (is_string($valor)) {
				$valor = mb_convert_encoding(
					$valor,
					'ISO-8859-1',
					'UTF-8'
				);
			}

			$registro[$campo] = $valor;
		}

		$datos_ml[] = $registro;
	}

	if (!empty($datos_ml)) {
		$form->set_datos($datos_ml);
	}
}


//-----------------------------------------------------------------------------------
	//---- form_equipo de catedra -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

function conf__equipo_de_catedra(toba_ei_formulario_ml $form)
{
	// Si hay filas pendientes, mostrarlas sin recargar las originales.
	$datos_pendientes = toba::memoria()->get_dato_operacion(
		'datos_equipo_de_catedra_pendientes'
	);

	if (is_array($datos_pendientes)) {
		$form->set_datos($datos_pendientes);
	} else {
		// Cargar los datos guardados en la base.
		$datos_programa = $this->edicion_obtener_datos_programa(null);

		if (!$datos_programa) {
			return;
		}

		$datos_ml = array();

		// Responsable: primera fila, no se guarda en equipo_catedra.
		if (!empty($datos_programa['apellido_resp'])
			|| !empty($datos_programa['nombre_resp'])) {

			$cargo_resp = isset($datos_programa['cargo_resp'])
				? $datos_programa['cargo_resp'] : '';

			$cargo = substr($cargo_resp, 0, 3);
			$dedicacion = substr($cargo_resp, 3, 1);

			$datos_ml[] = array(
				'apellido' => mb_convert_encoding(
					$datos_programa['apellido_resp'],
					'ISO-8859-1',
					'UTF-8'
				),
				'nombre' => mb_convert_encoding(
					$datos_programa['nombre_resp'],
					'ISO-8859-1',
					'UTF-8'
				),
				'legajo' => mb_convert_encoding(
					isset($datos_programa['legajo_resp'])
						? $datos_programa['legajo_resp'] : '',
					'ISO-8859-1',
					'UTF-8'
				),
				'cargo' => mb_convert_encoding(
					$cargo,
					'ISO-8859-1',
					'UTF-8'
				),
				'dedicacion' => mb_convert_encoding(
					$dedicacion,
					'ISO-8859-1',
					'UTF-8'
				)
			);
		}

		// Integrantes guardados en equipo_catedra.
		if (!empty($datos_programa['equipo_catedra'])) {
			$json_utf8 = mb_convert_encoding(
				$datos_programa['equipo_catedra'],
				'UTF-8',
				'ISO-8859-1'
			);

			$equipo = json_decode($json_utf8, true);

			if (is_array($equipo)) {
				foreach ($equipo as $fila) {
					$datos_ml[] = array(
						'apellido' => mb_convert_encoding(
							isset($fila[0]) ? $fila[0] : '',
							'ISO-8859-1',
							'UTF-8'
						),
						'nombre' => mb_convert_encoding(
							isset($fila[1]) ? $fila[1] : '',
							'ISO-8859-1',
							'UTF-8'
						),
						'legajo' => mb_convert_encoding(
							isset($fila[2]) ? $fila[2] : '',
							'ISO-8859-1',
							'UTF-8'
						),
						'cargo' => mb_convert_encoding(
							isset($fila[3]) ? $fila[3] : '',
							'ISO-8859-1',
							'UTF-8'
						),
						'dedicacion' => mb_convert_encoding(
							isset($fila[4]) ? $fila[4] : '',
							'ISO-8859-1',
							'UTF-8'
						)
					);
				}
			}
		}

		if (!empty($datos_ml)) {
			$form->set_datos($datos_ml);
		}
	}

	// Bloquear la edición de los campos del responsable.
	echo '\n    <script>
	$(document).ready(function() {
		$("#0_ef_form_2651_equipo_de_catedraapellido").prop("disabled", true);
		$("#0_ef_form_2651_equipo_de_catedranombre").prop("disabled", true);
		$("#0_ef_form_2651_equipo_de_catedralegajo").prop("disabled", true);
		$("#0_ef_form_2651_equipo_de_catedracargo").prop("disabled", true);
		$("#0_ef_form_2651_equipo_de_catedradedicacion").prop("disabled", true);
	});
	</script>
	';
}
	
	
	
	
	
	
	
function evt__equipo_de_catedra__modificacion($datos)
{
		toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__equipo_de_catedra_modificacion'
	);
	// Conservar temporalmente todas las filas ingresadas,
	// incluida la primera fila del responsable.
	toba::memoria()->set_dato_operacion(
		'datos_equipo_de_catedra_pendientes',
		$datos
	);
	$equipo_utf8 = array();
	$indice = 0;
	foreach ($datos as $fila) {

		// La primera fila corresponde al responsable.
		// No debe guardarse en equipo_catedra.
		if ($indice == 0) {
			$indice++;
			continue;
		}
		$fila_utf8 = array();
		foreach ($fila as $campo => $valor) {
			if ($campo == 'x_dbr_clave') {
				continue;
			}
			if (is_string($valor)) {
				$valor = mb_convert_encoding(
					$valor,
					'UTF-8',
					'ISO-8859-1'
				);
			}

			$fila_utf8[$campo] = $valor;
		}

		$equipo_utf8[] = array(
			$fila_utf8['apellido'],
			$fila_utf8['nombre'],
			$fila_utf8['legajo'],
			$fila_utf8['cargo'],
			$fila_utf8['dedicacion']
		);

		$indice++;
	}

	$equipo_json_utf8 = json_encode(
		$equipo_utf8,
		JSON_UNESCAPED_UNICODE
	);

	if ($equipo_json_utf8 === false) {
		throw new toba_error(
			"Error al generar JSON: " . json_last_error_msg()
		);
	}

	$equipo_json_latin1 = mb_convert_encoding(
		$equipo_json_utf8,
		'ISO-8859-1',
		'UTF-8'
	);

	$datos_programa = $this->dep('datos')
		->tabla('programas')
		->get();

	$datos_programa['equipo_catedra'] = $equipo_json_latin1;

	$this->dep('datos')->tabla('programas')->set($datos_programa);
	$this->dep('datos')->tabla('programas')->sincronizar();

		toba::logger()->info(
		'DIAGNOSTICO: FIN evt__equipo_de_catedra_modificacion'
	);
}
	
	
	
	
	
	
	
	
	
	
	
	

	//-----------------------------------------------------------------------------------
	//---- form_datos_materia -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
function conf__form_datos_materia(catedras_ei_formulario $form)
{
	$datos = $this->edicion_obtener_datos_programa(null);

	if (!$datos) {
		return;
	}

	$form->set_datos($datos);
}
	
	

	//-----------------------------------------------------------------------------------
	//---- formulario_con_todo -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	
function conf__formulario_con_todo(toba_ei_formulario $form)
{
	echo '<link rel="stylesheet" type="text/css" href="/toba/proyectos/catedras/www/skins/custom/custom.css">';

	$datos_programa = $this->edicion_obtener_datos_programa(
		'datos_formulario_con_todo_pendientes'
	);

	if ($datos_programa) {

			// Guardar los datos del programa.
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
			$this->cod_carrera_selec = $datos_programa['cod_carrera'];
			$this->id_programa_selec = $datos_programa['id_programa'];

			$this->firma_doc_selec = $datos_programa['firma_doc'];
			$this->firma_dto_selec = $datos_programa['firma_dto'];
			$this->firma_sac_selec = $datos_programa['firma_sac'];

			// Almacenar en memoria los datos del programa.
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
			toba::memoria()->set_dato_operacion('firma_doc_selec', $this->firma_doc_selec);
			toba::memoria()->set_dato_operacion('firma_dto_selec', $this->firma_dto_selec);
			toba::memoria()->set_dato_operacion('firma_sac_selec', $this->firma_sac_selec);

			// Establecer los datos en el formulario.
			$form->set_datos($datos_programa);

			// Definir carreras que no muestran horas teóricas prácticas.
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			if (in_array($this->cod_carrera_selec, $carreras_coneau)) {
				// Hacer el campo solo lectura.
				$form->set_solo_lectura(array('horas_teoricopracticas'), true);
			} else {
				// Asegurarse de que el campo sea editable.
				$form->set_solo_lectura(array('horas_teoricopracticas'), false);
			}
	} else {
		// Si no se pudieron recuperar los datos, eliminar el evento de eliminar.
		$this->pantalla()->eliminar_evento('eliminar');
	}

	
// Mostrar el mensaje inicial solamente cuando no hay datos
// pendientes de una validacion fallida.
$datos_pendientes = toba::memoria()->get_dato_operacion(
	'datos_formulario_con_todo_pendientes'
);

if (!is_array($datos_pendientes)) {

	toba::notificacion()->agregar(
		"En esta sección usted puede completar los datos del Programa, guardar los cambios para seguir completando en otra ocasión y, cuando esté listo, firmar electrónicamente y enviar al Departamento",
		'info'
	);

}    
	
	
}

	
	
	
////// form con todo modif ***************************************************************************


		///////////////////////////////////////////////////////////////////////
	// EVENTO: modificacion del formulario principal (formulario_con_todo)
	//
	// Este formulario se muestra DEBAJO del formulario 3, por lo que su
	// evento corre DESPUES del evento del formulario 3.
	//
	// Aca se hacen TODAS las validaciones ANTES de sincronizar, de modo
	// que si algo falta no se guarde nada y se conserve en memoria todo lo
	// que el usuario haya cargado en la pantalla.
	//
	// Ademas, en este mismo UPDATE se guardan el estado, la firma y el
	// comentario que preparo el formulario 3. De esa forma la fila de
	// 'programas' se modifica una sola vez y no aparece el error de
	// concurrencia.
	///////////////////////////////////////////////////////////////////////
// Reemplazo completo de evt__formulario_con_todo__modificacion($datos).
// Copiar el contenido de esta funcion dentro de ci_programas_y_planif.php.

	function evt__formulario_con_todo__modificacion($datos)
	{
		// Diagnostico: campos del POST relacionados con el estado.
		$claves_estado = array();

		foreach ($_POST as $clave_post => $valor_post) {

			if (strpos($clave_post, 'estado') === false) {
				continue;
			}

			if (is_array($valor_post)) {
				$claves_estado[$clave_post] = 'ARRAY';
			} else {
				$claves_estado[$clave_post] = $valor_post;
			}
		}

		toba::logger()->info(
			'DIAGNOSTICO POST ESTADO: '
			. print_r($claves_estado, true)
		);

		toba::logger()->info(
			'DIAGNOSTICO: INICIO evt__formulario_con_todo__modificacion'
		);

		// -------------------------------------------------------------------
		// 1. Recuperar el ID del programa seleccionado.
		// -------------------------------------------------------------------
		if (!isset($this->id_programa_seleccionado)
			|| $this->id_programa_seleccionado == '') {

			$this->id_programa_seleccionado =
				toba::memoria()->get_dato_operacion(
					'id_programa_seleccionado'
				);
		}

		// Mantener el ID disponible para las demas funciones.
		if (isset($this->id_programa_seleccionado)
			&& $this->id_programa_seleccionado != '') {

			toba::memoria()->set_dato_operacion(
				'id_programa_seleccionado',
				$this->id_programa_seleccionado
			);
		}

		$id_programa = $this->id_programa_seleccionado;

		if (empty($id_programa)) {
			throw new toba_error(
				'No se pudo identificar el programa seleccionado.'
			);
		}

		// -------------------------------------------------------------------
		// 2. Datos del programa en la base.
		//    Todavia no se guardo nada, por lo que el estado que hay aqui
		//    es el estado con el que se cargo la pantalla.
		// -------------------------------------------------------------------
		$programa = $this->dep('datos')->tabla('programas')
			->get_datos_programa_integrado($id_programa);

		if (!$programa) {
			throw new toba_error(
				'No se encontraron los datos del programa.'
			);
		}

		$estado_al_cargar = isset($programa['estado'])
			? $programa['estado'] : '';

		// Queda memorizado para que el formulario 3 sepa cual era el estado
		// original.
		toba::memoria()->set_dato_operacion(
			'estado_programa_al_cargar',
			$estado_al_cargar
		);

		// -------------------------------------------------------------------
		// 3. Estado que quiere dejar el programa.
		//    Se busca en todos los formularios de la pantalla y se prioriza
		//    el valor que difiere del estado con el que se cargo (o sea, el
		//    cambio que hizo el usuario).
		// -------------------------------------------------------------------
		$candidatos_estado = array();

		foreach ($_POST as $clave_post => $valor_post) {

			if (is_array($valor_post)) {
				continue;
			}

			// Solo campos de formularios de esta pantalla.
			if (strpos($clave_post, 'ef_form_') !== 0) {
				continue;
			}

			if (substr($clave_post, -6) === 'estado') {
				$candidatos_estado[] = $valor_post;
			}
		}

		if (isset($datos['estado']) && $datos['estado'] !== '') {
			$candidatos_estado[] = $datos['estado'];
		}

		$estado_nuevo = $estado_al_cargar;

		foreach ($candidatos_estado as $candidato_estado) {
			if ($candidato_estado !== ''
				&& $candidato_estado !== $estado_al_cargar) {
				$estado_nuevo = $candidato_estado;
			}
		}

		// Si no hay un cambio en la pantalla, usar el estado que preparo el
		// formulario 3.
		if ($estado_nuevo === $estado_al_cargar) {

			$estado_a_aplicar = toba::memoria()->get_dato_operacion(
				'estado_nuevo_a_aplicar'
			);

			if (isset($estado_a_aplicar) && !is_array($estado_a_aplicar)
				&& $estado_a_aplicar !== '') {
				$estado_nuevo = $estado_a_aplicar;
			}
		}

		toba::memoria()->set_dato_operacion(
			'estado_nuevo_a_aplicar',
			$estado_nuevo
		);

		// -------------------------------------------------------------------
		// 4. Comentario nuevo tipeado en la pantalla.
		// -------------------------------------------------------------------
		$comentario_nuevo = '';

		foreach ($_POST as $clave_post => $valor_post) {

			if (is_array($valor_post)) {
				continue;
			}

			if (strpos($clave_post, 'ef_form_') !== 0) {
				continue;
			}

			// El comentario nuevo termina en 'comentario'. El historial
			// termina en 'comentarios' y no se toma como comentario.
			if (substr($clave_post, -10) === 'comentario') {

				if (trim($valor_post) !== '') {
					$comentario_nuevo = trim($valor_post);
				}
			}
		}

		// -------------------------------------------------------------------
		// 5. Campos del segundo formulario que pertenecen a planificaciones.
		// -------------------------------------------------------------------
		$campos_planificacion = array(
			'actividades_internas',
			'actividades_externas',
			'libros_pub',
			'apuntes_pub',
			'guia_trabajos_pub',
			'publicaciones_periodicas'
		);

		// Recuperar los valores temporales del segundo formulario.
		$datos_2_pendientes = toba::memoria()->get_dato_operacion(
			'datos_2_formulario_con_todo_pendientes'
		);

		if (!is_array($datos_2_pendientes)) {
			$datos_2_pendientes = array();
		}

		// Recuperar directamente desde POST los campos del segundo formulario.
		foreach ($campos_planificacion as $campo) {
			$clave_post = 'ef_form_2654_2_formulario_con_todo' . $campo;

			if (array_key_exists($clave_post, $_POST)) {
				$datos_2_pendientes[$campo] = $_POST[$clave_post];
			} elseif (array_key_exists($campo, $datos)) {
				$datos_2_pendientes[$campo] = $datos[$campo];
			}
		}

		// -------------------------------------------------------------------
		// 6. Recuperar la bibliografia pendiente, si existe.
		// -------------------------------------------------------------------
		$datos_bibliografia_pendientes =
			toba::memoria()->get_dato_operacion(
				'datos_bibliografia_pedida_pendientes'
			);

		$clave_lista_bibliografia =
			'js_form_2664_bibliografia_pedida_listafilas';

		// Solo guardar la bibliografia si el multilinea fue enviado
		// o si hay datos pendientes de una validacion anterior.
		$guardar_bibliografia_pedida =
			array_key_exists($clave_lista_bibliografia, $_POST)
			|| is_array($datos_bibliografia_pendientes);

		// Si se recibio el multilinea por POST, reconstruir todas sus filas.
		if (array_key_exists($clave_lista_bibliografia, $_POST)) {

			$datos_bibliografia_pendientes = array();

			$filas_bibliografia = $_POST[$clave_lista_bibliografia];
			$indices_bibliografia = explode('_', $filas_bibliografia);

			foreach ($indices_bibliografia as $indice_fila) {

				if (!is_numeric($indice_fila)) {
					continue;
				}

				$prefijo = $indice_fila
					. '_ef_form_2664_bibliografia_pedida';

				$datos_bibliografia_pendientes[] = array(
					'titulo' => isset($_POST[$prefijo . 'titulo'])
						? $_POST[$prefijo . 'titulo'] : '',

					'autores' => isset($_POST[$prefijo . 'autores'])
						? $_POST[$prefijo . 'autores'] : '',

					'editorial' => isset($_POST[$prefijo . 'editorial'])
						? $_POST[$prefijo . 'editorial'] : '',

					'edicion' => isset($_POST[$prefijo . 'edicion'])
						? $_POST[$prefijo . 'edicion'] : '',

					'biblioteca' => isset($_POST[$prefijo . 'biblioteca'])
						? $_POST[$prefijo . 'biblioteca'] : '',

					'isbn' => isset($_POST[$prefijo . 'isbn'])
						? $_POST[$prefijo . 'isbn'] : ''
				);
			}
		}

		if (!is_array($datos_bibliografia_pendientes)) {
			$datos_bibliografia_pendientes = array();
		}

		// -------------------------------------------------------------------
		// 7. Datos de las horas.
		// -------------------------------------------------------------------
		$horas_totales = isset($datos['horas_totales'])
			? $datos['horas_totales'] : 0;

		$horas_practicas = isset($datos['horas_practicas'])
			? $datos['horas_practicas'] : 0;

		$horas_teoricas = isset($datos['horas_teoricas'])
			? $datos['horas_teoricas'] : 0;

		$horas_teoricopracticas = isset($datos['horas_teoricopracticas'])
			? $datos['horas_teoricopracticas'] : 0;

		$cod_carrera = isset($datos['cod_carrera'])
			? $datos['cod_carrera'] : '';

		$carreras_coneau = array(
			'LBIB', 'LENB', 'ICIB', 'IELB',
			'IETB', 'IMEB', 'IPEB', 'IQUB'
		);

		if (in_array($cod_carrera, $carreras_coneau)) {
			$subtotal_horas = $horas_practicas + $horas_teoricas;
		} else {
			$subtotal_horas = $horas_practicas
				+ $horas_teoricas
				+ $horas_teoricopracticas;
		}

		// -------------------------------------------------------------------
		// 8. Multilineas que deben conservarse si falla una validacion.
		//    'columna' es el campo de la tabla planificaciones (solo para
		//    los multilinea que forman parte de la planificacion).
		// -------------------------------------------------------------------
		$multilineas = array(
			'equipo_de_catedra' => array(
				'id' => 2651,
				'clave' => 'datos_equipo_de_catedra_pendientes',
				'campos' => array(
					'apellido', 'nombre', 'legajo', 'cargo', 'dedicacion'
				)
			),
			'dist_hor_planif' => array(
				'id' => 2658,
				'clave' => 'datos_dist_hor_planif_pendientes',
				'columna' => 'dist_horaria_planif',
				'campos' => array('dia', 'desde', 'hasta', 'tipo')
			),
			'horario_de_consulta' => array(
				'id' => 2659,
				'clave' => 'datos_horario_de_consulta_pendientes',
				'columna' => 'horarios_consulta',
				'campos' => array(
					'docente', 'legajo', 'dia', 'hora', 'lugar'
				)
			),
			'otras_tareas' => array(
				'id' => 2662,
				'clave' => 'datos_otras_tareas_pendientes',
				'columna' => 'otras_tareas',
				'campos' => array(
					'apellido', 'nombre', 'actividad', 'horas', 'descripcion'
				)
			)
		);

		// -------------------------------------------------------------------
		// 9. Filas de cada multilinea: primero lo que esta en pantalla,
		//    si no hay, lo que quedo pendiente de una validacion anterior.
		// -------------------------------------------------------------------
		$filas_multilineas = array();

		foreach ($multilineas as $nombre_ml => $config) {

			$clave_lista = 'js_form_'
				. $config['id'] . '_'
				. $nombre_ml . '_listafilas';

			$filas = array();

			if (array_key_exists($clave_lista, $_POST)) {

				$indices_ml = explode('_', $_POST[$clave_lista]);

				foreach ($indices_ml as $indice_fila) {

					if (!is_numeric($indice_fila)) {
						continue;
					}

					$prefijo = $indice_fila
						. '_ef_form_'
						. $config['id'] . '_'
						. $nombre_ml;

					$fila_ml = array();
					$fila_con_datos = false;

					foreach ($config['campos'] as $campo) {

						$valor_campo = isset($_POST[$prefijo . $campo])
							? $_POST[$prefijo . $campo] : '';

						$fila_ml[$campo] = $valor_campo;

						if (!is_array($valor_campo)
							&& trim($valor_campo) !== '') {
							$fila_con_datos = true;
						}
					}

					if ($fila_con_datos) {
						$filas[] = $fila_ml;
					}
				}
			} else {

				$filas_previas = toba::memoria()->get_dato_operacion(
					$config['clave']
				);

				if (is_array($filas_previas)) {
					$filas = $filas_previas;
				}
			}

			$filas_multilineas[$config['clave']] = $filas;
		}

		// -------------------------------------------------------------------
		// 10. VALIDACIONES.
		//     Se arman los errores y recien despues, si hay alguno, se
		//     conservan los cambios y se corta sin guardar nada.
		// -------------------------------------------------------------------
		$mensaje_error = '';

		// 10.1. La suma de horas tiene que coincidir.
		if ($subtotal_horas != $horas_totales) {
			$mensaje_error =
				'La suma de horas no coincide con la carga horaria total '
				. 'por plan de estudios, por favor corrija.';
		}

		// 10.2. Para pasar a Departamento tienen que estar completos los
		//       campos obligatorios del programa y los multilinea de la
		//       planificacion.
		if ($mensaje_error === ''
			&& $estado_nuevo == 'depto'
			&& $estado_al_cargar != 'depto') {

			// Campos obligatorios del programa.
			$campos_obligatorios = array(
				'fundamentacion',
				'objetivos',
				'programa_analitico',
				'bibliografia',
				'propuesta_metodologica',
				'evaluacion_acreditacion',
				'distribucion_horaria',
				'horas_practicas',
				'horas_teoricas',
				'cronograma_tentativo'
			);

			// Valores de los campos obligatorios que estan en pantalla.
			$valores_pantalla = array();

			foreach ($_POST as $clave_post => $valor_post) {

				if (is_array($valor_post)) {
					continue;
				}

				// Solo campos de formularios (no filas de multilinea).
				if (strpos($clave_post, 'ef_form_') !== 0) {
					continue;
				}

				foreach ($campos_obligatorios as $campo) {

					$largo = strlen($campo);

					if (strlen($clave_post) > $largo
						&& substr($clave_post, -$largo) === $campo) {
						$valores_pantalla[$campo] = $valor_post;
					}
				}
			}

			$faltantes = array();

			foreach ($campos_obligatorios as $campo) {

				if (array_key_exists($campo, $datos)) {
					// Valor cargado en este formulario.
					$valor = $datos[$campo];
				} elseif (array_key_exists($campo, $valores_pantalla)) {
					// Valor cargado en otro formulario de la pantalla.
					$valor = $valores_pantalla[$campo];
				} else {
					// Valor ya guardado en la base.
					$valor = isset($programa[$campo])
						? $programa[$campo] : '';
				}

				if (!isset($valor) || is_array($valor)
					|| trim($valor) === '') {
					$faltantes[] = $campo;
				}
			}

			// Buscar la planificacion vinculada al programa.
			$sql = "
				SELECT dist_horaria_planif,
						horarios_consulta,
						otras_tareas
				FROM planificaciones
				WHERE id_prog_planif = " . quote($id_programa);

			$planificacion = toba::db('catedras')->consultar_fila($sql);

			if (!$planificacion) {
				$mensaje_error =
					'No se encontró la planificación del programa.';
			} else {

				// Revisar los tres multilinea obligatorios de la planificacion.
				foreach ($multilineas as $nombre_ml => $config) {

					// Equipo de catedra no forma parte de esta validacion.
					if (!isset($config['columna'])) {
						continue;
					}

					$clave_lista = 'js_form_'
						. $config['id'] . '_'
						. $nombre_ml . '_listafilas';

					$filas_ml = isset($filas_multilineas[$config['clave']])
						&& is_array($filas_multilineas[$config['clave']])
						? $filas_multilineas[$config['clave']]
						: array();

					// Determinar si el multilinea fue enviado en esta
					// operacion o si hay datos temporales de un intento previo.
					// Si fue enviado vacio, NO se deben recuperar los datos
					// anteriores de la base: el usuario pudo haberlos borrado.
					$fue_enviado = array_key_exists($clave_lista, $_POST);

					$filas_pendientes = toba::memoria()->get_dato_operacion(
						$config['clave']
					);

					$hay_pendientes = is_array($filas_pendientes);

					// Buscar contenido real en las filas recibidas o pendientes.
					$tiene_datos = false;

					foreach ($filas_ml as $fila_ml) {
						if (!is_array($fila_ml)) {
							continue;
						}

						foreach ($fila_ml as $valor_ml) {
							if (is_scalar($valor_ml)
								&& trim((string) ($valor_ml)) !== '') {
								$tiene_datos = true;
								break 2;
							}
						}
					}

					// Si el multilinea fue enviado o hay datos pendientes,
					// esos datos son los que mandan, incluso si estan vacios.
					if ($fue_enviado || $hay_pendientes) {
						if (!$tiene_datos) {
							$faltantes[] = $config['columna'];
						}
						continue;
					}

					// Si no fue enviado ni hay datos pendientes, revisar la base.
					$valor_base = isset($planificacion[$config['columna']])
						? trim((string) ($planificacion[$config['columna']]))
						: '';

					$base_tiene_datos = false;

					if ($valor_base !== '') {
						// Los JSON se guardan en ISO-8859-1. Convertir solo
						// cuando el contenido no sea ya UTF-8 valido.
						$valor_base_utf8 = $valor_base;

						if (!mb_check_encoding($valor_base_utf8, 'UTF-8')) {
							$valor_base_utf8 = mb_convert_encoding(
								$valor_base_utf8,
								'UTF-8',
								'ISO-8859-1'
							);
						}

						// Un JSON [] (tambien con espacios) representa vacio.
						$base_decodificada = json_decode(
							$valor_base_utf8,
							true
						);

						if (json_last_error() === JSON_ERROR_NONE
							&& is_array($base_decodificada)
							&& count($base_decodificada) > 0) {
							$base_tiene_datos = true;
						}
					}

					if (!$base_tiene_datos) {
						$faltantes[] = $config['columna'];
					}
				}

				if (!empty($faltantes)) {
					$mensaje_error =
						'No se puede enviar el programa a Departamento. '
						. 'Faltan completar campos obligatorios: '
						. implode(', ', $faltantes) . '.';
				}
			}
		}


		// -------------------------------------------------------------------
		// 11. Si hay un error se conservan todos los cambios temporales
		//     y no se guarda nada.
		// -------------------------------------------------------------------
		if ($mensaje_error !== '') {

			// Cambios del formulario principal.
			toba::memoria()->set_dato_operacion(
				'datos_formulario_con_todo_pendientes',
				$datos
			);

			// Cambios del segundo formulario.
			toba::memoria()->set_dato_operacion(
				'datos_2_formulario_con_todo_pendientes',
				$datos_2_pendientes
			);

			// Bibliografia ingresada.
			toba::memoria()->set_dato_operacion(
				'datos_bibliografia_pedida_pendientes',
				$datos_bibliografia_pendientes
			);

			// Filas de cada multilinea.
			foreach ($filas_multilineas as $clave_memoria => $filas) {
				toba::memoria()->set_dato_operacion(
					$clave_memoria,
					$filas
				);
			}

			// Comentario nuevo.
			if ($comentario_nuevo !== '') {
				toba::memoria()->set_dato_operacion(
					'comentario_nuevo_pendiente',
					$comentario_nuevo
				);
			}

			// Estado que se queria aplicar.
			if ($estado_nuevo !== '') {
				toba::memoria()->set_dato_operacion(
					'estado_nuevo_pendiente',
					$estado_nuevo
				);
			}
			// Limpiar el estado preparado para esta operacion.
			// Si hubo un error, no debe reutilizarse en el siguiente guardado.
			toba::memoria()->set_dato_operacion(
			'estado_nuevo_a_aplicar',
					null
				);     


			throw new toba_error($mensaje_error);
		}

		// -------------------------------------------------------------------
		// 12. Aplicar el estado, la firma y el comentario que preparo el
		//     formulario 3. Se aplican antes de sincronizar para que salgan
		//     en el mismo UPDATE que el resto de los datos.
		// -------------------------------------------------------------------
		if ($estado_nuevo !== '') {
			$datos['estado'] = $estado_nuevo;
		}

		//desde
		// Armar el historial directamente en el evento principal.
// No depender del orden de ejecucion de otros formularios.
if ($comentario_nuevo !== '') {

	$comentarios_actuales = isset($programa['comentarios'])
		? $programa['comentarios'] : '';

	// Asegurar que el texto sea UTF-8 valido.
	if (!mb_check_encoding($comentario_nuevo, 'UTF-8')) {
		$comentario_nuevo = mb_convert_encoding(
			$comentario_nuevo,
			'UTF-8',
			'ISO-8859-1'
		);
	}

	$fecha = new DateTime(
		'now',
		new DateTimeZone('America/Argentina/Buenos_Aires')
	);

	$timestamp = $fecha->format('Y-m-d H:i:s');
	$usuario_id = toba::usuario()->get_id();
	$nombre_completo = toba::usuario()->get_nombre();

	$comentario_html = nl2br(
		htmlspecialchars(
			$comentario_nuevo,
			ENT_QUOTES,
			'UTF-8'
		)
	);

	$nuevo_comentario =
		"<div style='border:1px solid #ccc;padding:10px;margin-top:10px;'>"
		. "<strong>"
		. htmlspecialchars($nombre_completo, ENT_QUOTES, 'UTF-8')
		. " ("
		. htmlspecialchars($usuario_id, ENT_QUOTES, 'UTF-8')
		. ") - "
		. $timestamp
		. "</strong><br><p style='margin:5px 0;'>"
		. $comentario_html
		. "</p></div>";

	// Conservar el historial anterior y agregar el comentario nuevo.
	$datos['comentarios'] = $comentarios_actuales . $nuevo_comentario;
}
		
		
		////hasta
		$firma_a_aplicar = toba::memoria()->get_dato_operacion(
			'firma_a_aplicar'
		);

		if (isset($firma_a_aplicar)
			&& !is_array($firma_a_aplicar)
			&& $firma_a_aplicar !== '') {
			$datos['firma_doc'] = $firma_a_aplicar;
		}



		// -------------------------------------------------------------------
		// 13. Todo validado: recien ahora se guarda.
		// -------------------------------------------------------------------

		// Para las carreras CONEAU, no guardar horas teorico-practicas.
		if (in_array($cod_carrera, $carreras_coneau)) {
			$datos['horas_teoricopracticas'] = 0;
		}

		// Guardar los datos del programa en programas.
		$this->dep('datos')->tabla('programas')->set($datos);
		$this->dep('datos')->tabla('programas')->sincronizar();

		// Buscar la planificacion vinculada al programa.
		$sql = "
			SELECT id_planificacion
			FROM planificaciones
			WHERE id_prog_planif = " . quote($id_programa);

		$fila_planif = toba::db('catedras')->consultar_fila($sql);

		if (empty($fila_planif['id_planificacion'])) {
			throw new toba_error(
				'No se encontró la planificación correspondiente a este programa.'
			);
		}

		$id_planificacion = $fila_planif['id_planificacion'];

		// Preparar las actualizaciones de los campos del segundo formulario.
		$actualizaciones = array();
		
// Si se confirma el cambio de estado de docente a depto,
// actualizar tambien el estado de la planificacion.
if ($estado_al_cargar === 'docente'
	&& $estado_nuevo === 'depto') {

	$actualizaciones[] = 'estado_planificacion = '
		. quote('depto');
}
		

		foreach ($campos_planificacion as $campo) {
			if (array_key_exists($campo, $datos_2_pendientes)) {
				$actualizaciones[] = $campo . ' = '
					. quote($datos_2_pendientes[$campo]);
			}
		}

		// Preparar el JSON de bibliografia si el multilinea fue enviado.
		if ($guardar_bibliografia_pedida) {

			$bibliografia_json = array();

			foreach ($datos_bibliografia_pendientes as $fila) {

				$titulo = isset($fila['titulo'])
					? $fila['titulo'] : '';

				$autores = isset($fila['autores'])
					? $fila['autores'] : '';

				$editorial = isset($fila['editorial'])
					? $fila['editorial'] : '';

				$edicion = isset($fila['edicion'])
					? $fila['edicion'] : '';

				$biblioteca = isset($fila['biblioteca'])
					? $fila['biblioteca'] : '';

				$isbn = isset($fila['isbn'])
					? $fila['isbn'] : '';

				// Asegurar UTF-8 antes de generar el JSON.
				$campos_bibliografia = array(
					'titulo' => $titulo,
					'autores' => $autores,
					'editorial' => $editorial,
					'edicion' => $edicion,
					'biblioteca' => $biblioteca,
					'isbn' => $isbn
				);

				foreach ($campos_bibliografia as $campo => $valor) {
					if (!mb_check_encoding($valor, 'UTF-8')) {
						$campos_bibliografia[$campo] =
							mb_convert_encoding(
								$valor,
								'UTF-8',
								'ISO-8859-1'
							);
					}
				}

				// Guardar las filas sin encabezados, en el orden de las columnas.
				$bibliografia_json[] = array(
					$campos_bibliografia['titulo'],
					$campos_bibliografia['autores'],
					$campos_bibliografia['editorial'],
					$campos_bibliografia['edicion'],
					$campos_bibliografia['biblioteca'],
					$campos_bibliografia['isbn']
				);
			}

			$json_bibliografia_utf8 = json_encode(
				$bibliografia_json,
				JSON_UNESCAPED_UNICODE
			);

			if ($json_bibliografia_utf8 === false) {
				throw new toba_error(
					'Error al generar el JSON de bibliografía: '
					. json_last_error_msg()
				);
			}

			// Mantener la codificacion utilizada por los otros JSON del CI.
			$json_bibliografia_iso = mb_convert_encoding(
				$json_bibliografia_utf8,
				'ISO-8859-1',
				'UTF-8'
			);

			$actualizaciones[] = 'bibliografia_pedida = '
				. quote($json_bibliografia_iso);
		}

		// Guardar los campos exclusivamente en planificaciones.
		if (!empty($actualizaciones)) {
			$sql_update = "
				UPDATE planificaciones
				SET " . implode(', ', $actualizaciones) . "
				WHERE id_planificacion = " . quote($id_planificacion);

			toba::db('catedras')->ejecutar($sql_update);
		}

		// -------------------------------------------------------------------
		// 14. Limpiar los datos pendientes despues del guardado exitoso.
		// -------------------------------------------------------------------
		toba::memoria()->set_dato_operacion(
			'datos_formulario_con_todo_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_2_formulario_con_todo_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_equipo_de_catedra_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_dist_hor_planif_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_horario_de_consulta_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_otras_tareas_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'datos_bibliografia_pedida_pendientes',
			null
		);

		toba::memoria()->set_dato_operacion(
			'comentario_nuevo_pendiente',
			null
		);

		toba::memoria()->set_dato_operacion(
			'estado_nuevo_pendiente',
			null
		);

		toba::memoria()->set_dato_operacion(
			'comentarios_a_aplicar',
			null
		);

		toba::memoria()->set_dato_operacion(
			'firma_a_aplicar',
			null
		);

		toba::logger()->info(
			'DIAGNOSTICO: FIN evt__formulario_con_todo__modificacion'
		);
	}  

	
	
	
	
	
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
///////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////    

	
	
	
	
//************************************************************************************************************
// dist hor planif
//***********************************    


function conf__dist_hor_planif(toba_ei_formulario_ml $form)
{
	$this->edicion_cargar_multilinea(
		$form,
		'dist_horaria_planif',
		array('dia', 'desde', 'hasta', 'tipo'),
		'datos_dist_hor_planif_pendientes'
	);
}
	
	
	


function evt__dist_hor_planif__modificacion($datos)
{
	toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__dist_hor_planif_modificacion'
	);    
	
	// Conservar temporalmente los datos del formulario.
	toba::memoria()->set_dato_operacion(
		'datos_dist_hor_planif_pendientes',
		$datos
	);

	// Recuperar el programa seleccionado.
	$id_programa = $this->id_programa_seleccionado;

	if (empty($id_programa)) {
		$id_programa = toba::memoria()->get_dato_operacion(
			'id_programa_seleccionado'
		);
	}

	if (empty($id_programa)) {
		throw new toba_error(
			'No se pudo identificar el programa seleccionado.'
		);
	}

	// Buscar la planificación correspondiente al programa.
	$sql = "
		SELECT id_planificacion
		FROM planificaciones
		WHERE id_prog_planif = " . quote($id_programa);

	$fila_planif = toba::db('catedras')->consultar_fila($sql);

	if (empty($fila_planif['id_planificacion'])) {
		throw new toba_error(
			'No se encontró la planificación correspondiente a este programa.'
		);
	}

	$id_planificacion = $fila_planif['id_planificacion'];

	// Preparar los datos del multilinea.
	$distribucion = array();

	foreach ($datos as $fila) {
		$dia = isset($fila['dia']) ? $fila['dia'] : '';
		$desde = isset($fila['desde']) ? $fila['desde'] : '';
		$hasta = isset($fila['hasta']) ? $fila['hasta'] : '';
		$tipo = isset($fila['tipo']) ? $fila['tipo'] : '';

		// Convertir solamente los valores que no sean UTF-8 válido.
		if (!mb_check_encoding($dia, 'UTF-8')) {
			$dia = mb_convert_encoding(
				$dia,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($desde, 'UTF-8')) {
			$desde = mb_convert_encoding(
				$desde,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($hasta, 'UTF-8')) {
			$hasta = mb_convert_encoding(
				$hasta,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($tipo, 'UTF-8')) {
			$tipo = mb_convert_encoding(
				$tipo,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		$distribucion[] = array(
			$dia,
			$desde,
			$hasta,
			$tipo
		);
	}

	// Generar el JSON en UTF-8.
	$json_utf8 = json_encode(
		$distribucion,
		JSON_UNESCAPED_UNICODE
	);

	if ($json_utf8 === false) {
		throw new toba_error(
			'Error al generar el JSON de distribución horaria: '
			. json_last_error_msg()
		);
	}

	// Guardar la distribución horaria en la planificación.
	$json_iso = mb_convert_encoding(
		$json_utf8,
		'ISO-8859-1',
		'UTF-8'
	);

	$sql_update = "
		UPDATE planificaciones
		SET dist_horaria_planif = " . quote($json_iso) . "
		WHERE id_planificacion = " . quote($id_planificacion);

	toba::db('catedras')->ejecutar($sql_update);

	// Limpiar los datos pendientes después de guardar correctamente.
	toba::memoria()->set_dato_operacion(
		'datos_dist_hor_planif_pendientes',
		null
	);

	toba::logger()->info(
		'DIAGNOSTICO: FIN evt__dist_hor_planif_modificacion'
	);    
	


}
	
	

	
	
//*********************
// HORARIOS DE CONSULTA
//*********************
	

function conf__horario_de_consulta(toba_ei_formulario_ml $form)
{
	$this->edicion_cargar_multilinea(
		$form,
		'horarios_consulta',
		array('docente', 'legajo', 'dia', 'hora', 'lugar'),
		'datos_horario_de_consulta_pendientes'
	);
}


	
	
	//****MODIFICACION HORARIOS DE CONSULTA

function evt__horario_de_consulta__modificacion($datos)
{

	toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__HORARIO_DE_CONSULTA_modificacion'
	);    
	
	// Conservar temporalmente las filas ingresadas.
	toba::memoria()->set_dato_operacion(
		'datos_horario_de_consulta_pendientes',
		$datos
	);

	// Recuperar el programa seleccionado.
	$id_programa = $this->id_programa_seleccionado;

	if (empty($id_programa)) {
		$id_programa = toba::memoria()->get_dato_operacion(
			'id_programa_seleccionado'
		);
	}

	if (empty($id_programa)) {
		throw new toba_error(
			'No se pudo identificar el programa seleccionado.'
		);
	}

	// Buscar la planificación correspondiente.
	$sql = "
		SELECT id_planificacion
		FROM planificaciones
		WHERE id_prog_planif = " . quote($id_programa);

	$fila_planif = toba::db('catedras')->consultar_fila($sql);

	if (empty($fila_planif['id_planificacion'])) {
		throw new toba_error(
			'No se encontró la planificación correspondiente a este programa.'
		);
	}

	$id_planificacion = $fila_planif['id_planificacion'];

	// Preparar las filas como JSON UTF-8.
	$horarios = array();

	foreach ($datos as $fila) {
		$docente = isset($fila['docente'])
			? $fila['docente'] : '';
		$legajo = isset($fila['legajo'])
			? $fila['legajo'] : '';
		$dia = isset($fila['dia'])
			? $fila['dia'] : '';
		$hora = isset($fila['hora'])
			? $fila['hora'] : '';
		$lugar = isset($fila['lugar'])
			? $fila['lugar'] : '';

		$docente = mb_convert_encoding(
			$docente, 'UTF-8', 'ISO-8859-1'
		);
		$legajo = mb_convert_encoding(
			$legajo, 'UTF-8', 'ISO-8859-1'
		);
		$dia = mb_convert_encoding(
			$dia, 'UTF-8', 'ISO-8859-1'
		);
		$hora = mb_convert_encoding(
			$hora, 'UTF-8', 'ISO-8859-1'
		);
		$lugar = mb_convert_encoding(
			$lugar, 'UTF-8', 'ISO-8859-1'
		);

		$horarios[] = array(
			$docente,
			$legajo,
			$dia,
			$hora,
			$lugar
		);
	}

	$json_utf8 = json_encode(
		$horarios,
		JSON_UNESCAPED_UNICODE
	);

	if ($json_utf8 === false) {
		throw new toba_error(
			'Error al generar el JSON de horarios de consulta: '
			. json_last_error_msg()
		);
	}

	// Actualizar únicamente los horarios de consulta.
	$sql_update = "
		UPDATE planificaciones
		SET horarios_consulta = " . quote(
			mb_convert_encoding(
				$json_utf8,
				'ISO-8859-1',
				'UTF-8'
			)
		) . "
		WHERE id_planificacion = " . quote($id_planificacion);

	toba::db('catedras')->ejecutar($sql_update);

	// Limpiar los datos pendientes luego de guardar correctamente.
	toba::memoria()->set_dato_operacion(
		'datos_horario_de_consulta_pendientes',
		null
	);

	toba::logger()->info(
		'DIAGNOSTICO: FIN HORARIO_DE_CONSULTA_modificacion'
	);    
	


}    
	
	
	
//********************************
//   OTRAS TAREAS
//********************************
	

function conf__otras_tareas(toba_ei_formulario_ml $form)
{
	$this->edicion_cargar_multilinea(
		$form,
		'otras_tareas',
		array(
			'apellido',
			'nombre',
			'actividad',
			'horas',
			'descripcion'
		),
		'datos_otras_tareas_pendientes'
	);
}



function evt__otras_tareas__modificacion($datos)
{
		toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__OTRAS_TAREAS_modificacion'
	);    
	
	
	
	// Conservar temporalmente las filas ingresadas.
	toba::memoria()->set_dato_operacion(
		'datos_otras_tareas_pendientes',
		$datos
	);

	// Recuperar el programa seleccionado.
	$id_programa = $this->id_programa_seleccionado;

	if (empty($id_programa)) {
		$id_programa = toba::memoria()->get_dato_operacion(
			'id_programa_seleccionado'
		);
	}

	if (empty($id_programa)) {
		throw new toba_error(
			'No se pudo identificar el programa seleccionado.'
		);
	}

	// Buscar la planificación correspondiente.
	$sql = "
		SELECT id_planificacion
		FROM planificaciones
		WHERE id_prog_planif = " . quote($id_programa);

	$fila_planif = toba::db('catedras')->consultar_fila($sql);

	if (empty($fila_planif['id_planificacion'])) {
		throw new toba_error(
			'No se encontró la planificación correspondiente a este programa.'
		);
	}

	$id_planificacion = $fila_planif['id_planificacion'];

	// Preparar las filas como JSON UTF-8.
	$tareas = array();

	foreach ($datos as $fila) {
		$apellido = isset($fila['apellido'])
			? $fila['apellido'] : '';

		$nombre = isset($fila['nombre'])
			? $fila['nombre'] : '';

		$actividad = isset($fila['actividad'])
			? $fila['actividad'] : '';

		$horas = isset($fila['horas'])
			? $fila['horas'] : '';

		$descripcion = isset($fila['descripcion'])
			? $fila['descripcion'] : '';

		// Convertir solamente los textos que no sean UTF-8 válidos.
		if (!mb_check_encoding($apellido, 'UTF-8')) {
			$apellido = mb_convert_encoding(
				$apellido,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($nombre, 'UTF-8')) {
			$nombre = mb_convert_encoding(
				$nombre,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($actividad, 'UTF-8')) {
			$actividad = mb_convert_encoding(
				$actividad,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($descripcion, 'UTF-8')) {
			$descripcion = mb_convert_encoding(
				$descripcion,
				'UTF-8',
				'ISO-8859-1'
			);
		}

		$tareas[] = array(
			$apellido,
			$nombre,
			$actividad,
			$horas,
			$descripcion
		);
	}

	$json_utf8 = json_encode(
		$tareas,
		JSON_UNESCAPED_UNICODE
	);

	if ($json_utf8 === false) {
		throw new toba_error(
			'Error al generar el JSON de otras tareas: '
			. json_last_error_msg()
		);
	}

	// Actualizar únicamente la columna otras_tareas.
	$sql_update = "
		UPDATE planificaciones
		SET otras_tareas = " . quote(
			mb_convert_encoding(
				$json_utf8,
				'ISO-8859-1',
				'UTF-8'
			)
		) . "
		WHERE id_planificacion = " . quote($id_planificacion);

	toba::db('catedras')->ejecutar($sql_update);

	// Limpiar los datos pendientes luego de guardar correctamente.
	toba::memoria()->set_dato_operacion(
		'datos_otras_tareas_pendientes',
		null
	);

		toba::logger()->info(
		'DIAGNOSTICO: FIN evt__OTRAS_TAREAS_modificacion'
	);    

}

	

	
//********************    
// 2_formulario_con_todo
//*********************


function conf__2_formulario_con_todo(toba_ei_formulario $form)
{
	$datos = $this->edicion_obtener_datos_programa(
		'datos_2_formulario_con_todo_pendientes'
	);

	if (!$datos) {
		return;
	}

	$form->set_datos($datos);
}


function evt__2_formulario_con_todo__modificacion($datos)
{
	
		toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__2_FORMULARIO_CON_TODO_modificacion'
	);        
	
	// Conservar los datos ingresados en el segundo formulario.
	if (is_array($datos)) {
		toba::memoria()->set_dato_operacion(
			'datos_2_formulario_con_todo_pendientes',
			$datos
		);
	}

	
		toba::logger()->info(
		'DIAGNOSTICO: FIN evt__2_FORMULARIO_CON_TODO_modificacion'
		);


}


//**********************    
// bibliografia pedida
//***********************
	

function conf__bibliografia_pedida(toba_ei_formulario_ml $form)
{
	$this->edicion_cargar_multilinea(
		$form,
		'bibliografia_pedida',
		array(
			'titulo',
			'autores',
			'editorial',
			'edicion',
			'biblioteca',
			'isbn'
		),
		'datos_bibliografia_pedida_pendientes'
	);
}


function evt__bibliografia_pedida__modificacion($datos)
{
		
		toba::logger()->info(
		'DIAGNOSTICO: INICIO evt__BIBLIOGRAFIA_PEDIDA_modificacion'
			);   
	
	
	// Conservar temporalmente las filas ingresadas.
	toba::memoria()->set_dato_operacion(
		'datos_bibliografia_pedida_pendientes',
		$datos
	);

	// Recuperar el programa seleccionado.
	$id_programa = $this->id_programa_seleccionado;

	if (empty($id_programa)) {
		$id_programa = toba::memoria()->get_dato_operacion(
			'id_programa_seleccionado'
		);
	}

	if (empty($id_programa)) {
		throw new toba_error(
			'No se pudo identificar el programa seleccionado.'
		);
	}

	// Buscar la planificación correspondiente.
	$sql = "
		SELECT id_planificacion
		FROM planificaciones
		WHERE id_prog_planif = " . quote($id_programa);

	$fila_planif = toba::db('catedras')->consultar_fila($sql);

	if (empty($fila_planif['id_planificacion'])) {
		throw new toba_error(
			'No se encontró la planificación correspondiente a este programa.'
		);
	}

	$id_planificacion = $fila_planif['id_planificacion'];

	// Preparar las filas como JSON UTF-8.
	$bibliografia = array();

	foreach ($datos as $fila) {
		$titulo = isset($fila['titulo'])
			? $fila['titulo'] : '';

		$autores = isset($fila['autores'])
			? $fila['autores'] : '';

		$editorial = isset($fila['editorial'])
			? $fila['editorial'] : '';

		$edicion = isset($fila['edicion'])
			? $fila['edicion'] : '';

		$biblioteca = isset($fila['biblioteca'])
			? $fila['biblioteca'] : '';

		$isbn = isset($fila['isbn'])
			? $fila['isbn'] : '';

		// Convertir solamente los textos que no sean UTF-8 válidos.
		if (!mb_check_encoding($titulo, 'UTF-8')) {
			$titulo = mb_convert_encoding(
				$titulo, 'UTF-8', 'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($autores, 'UTF-8')) {
			$autores = mb_convert_encoding(
				$autores, 'UTF-8', 'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($editorial, 'UTF-8')) {
			$editorial = mb_convert_encoding(
				$editorial, 'UTF-8', 'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($edicion, 'UTF-8')) {
			$edicion = mb_convert_encoding(
				$edicion, 'UTF-8', 'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($biblioteca, 'UTF-8')) {
			$biblioteca = mb_convert_encoding(
				$biblioteca, 'UTF-8', 'ISO-8859-1'
			);
		}

		if (!mb_check_encoding($isbn, 'UTF-8')) {
			$isbn = mb_convert_encoding(
				$isbn, 'UTF-8', 'ISO-8859-1'
			);
		}

		$bibliografia[] = array(
			$titulo,
			$autores,
			$editorial,
			$edicion,
			$biblioteca,
			$isbn
		);
	}

	$json_utf8 = json_encode(
		$bibliografia,
		JSON_UNESCAPED_UNICODE
	);

	if ($json_utf8 === false) {
		throw new toba_error(
			'Error al generar el JSON de bibliografía: '
			. json_last_error_msg()
		);
	}

	// Actualizar únicamente bibliografia_pedida.
	$sql_update = "
		UPDATE planificaciones
		SET bibliografia_pedida = " . quote(
			mb_convert_encoding(
				$json_utf8,
				'ISO-8859-1',
				'UTF-8'
			)
		) . "
		WHERE id_planificacion = " . quote($id_planificacion);

	toba::db('catedras')->ejecutar($sql_update);

	// Limpiar los datos pendientes después de guardar correctamente.
	toba::memoria()->set_dato_operacion(
		'datos_bibliografia_pedida_pendientes',
		null
	);

		toba::logger()->info(
		'DIAGNOSTICO: FIN evt__BIBLIOGRAFIA_PEDIDA_modificacion'
			);   


}
	
	
	
	//*************************************************************************
	// 3_formulario_con_todo
	//---------------------------------------------------------------------------
//////////////////////////////////////////////////////////////////////////////////////////////////////    

/////////////////////////////////////////////////////////////////////////////////////////////    


	///////////////////////////////////////////////////////////////////////
	// CONFIGURACION: formulario 3 (3_formulario_con_todo)
	//
	// Ademas de lo que ya hacia, memoriza el estado con el que se cargo la
	// pantalla y, si una validacion fallo, vuelve a mostrar el estado que
	// el usuario habia elegido (asi no se pierde ese cambio).
	//
	// Este cambio es el que permite que el formulario 3 sepa si hay una
	// transicion de estado, ya que el formulario principal puede haber
	// guardado el estado nuevo antes de que corra el formulario 3.
	///////////////////////////////////////////////////////////////////////

	function conf__3_formulario_con_todo(toba_ei_formulario $form)
	{
		// Recuperar el programa seleccionado.
		$id_programa = '';

		if (isset($this->id_programa_seleccionado)) {
			$id_programa = $this->id_programa_seleccionado;
		}

		if (empty($id_programa)) {
			$id_programa = toba::memoria()->get_dato_operacion(
				'id_programa_seleccionado'
			);
		}

		if (empty($id_programa)) {
			return;
		}

		// Obtener los datos del programa.
		$datos_programa = $this->dep('datos')->tabla('programas')
			->get_datos_programa_integrado($id_programa);

		if (!$datos_programa) {
			return;
		}

		// Guardar el estado con el que se cargo la pantalla.
		toba::memoria()->set_dato_operacion(
			'estado_programa_al_cargar',
			isset($datos_programa['estado']) ? $datos_programa['estado'] : ''
		);

		// Recuperar el comentario pendiente si fallo una validacion.
		$comentario_pendiente = toba::memoria()->get_dato_operacion(
			'comentario_nuevo_pendiente'
		);

		if (is_string($comentario_pendiente)) {
			$datos_programa['comentario'] = $comentario_pendiente;
		} else {
			// Si no hay comentario pendiente, comenzar con el campo vacio.
			$datos_programa['comentario'] = '';
		}

		// Recuperar el estado que se quiso aplicar si fallo una validacion.
		$estado_pendiente = toba::memoria()->get_dato_operacion(
			'estado_nuevo_pendiente'
		);

		if (is_string($estado_pendiente) && $estado_pendiente !== '') {
			$datos_programa['estado'] = $estado_pendiente;
		}

		// Mostrar los datos en el formulario.
		$form->set_datos($datos_programa);
	}    
	
/////////////////////////////////////////////////////////////////////////////////////////////   
	///////////////////////////////////////////////////////////////////////
	// EVENTO: modificacion del formulario 3 (3_formulario_con_todo)
	//
	// Este formulario maneja el ESTADO del programa y los COMENTARIOS.
	//
	// MUY IMPORTANTE: en la pantalla este formulario esta ubicado ANTES del
	// formulario principal, por lo que su evento se dispara PRIMERO.
	//
	// Por eso ACA NO SE MODIFICA LA BASE DE DATOS. La tabla 'programas' la
	// actualiza completa el formulario principal, en un solo UPDATE. Si este
	// formulario modificara la fila antes, el formulario principal
	// encontraria la fila cambiada y apareceria el error
	// "Error de concurrencia en la edicion de los datos".
	//
	// Aca se valida, se arma el comentario nuevo (conservando todo el
	// historial) y la firma. Todo queda en memoria y el formulario
	// principal lo guarda junto con el resto de los datos.
	///////////////////////////////////////////////////////////////////////

	function evt__3_formulario_con_todo__modificacion($datos)
	{
		toba::logger()->info(
			'DIAGNOSTICO ESTADO: '
			. print_r($datos, true)
		);

		// Diagnostico: campos del POST relacionados con el comentario.
		$claves_comentario = array();

		foreach ($_POST as $clave_post => $valor_post) {

			if (strpos($clave_post, 'comentario') === false) {
				continue;
			}

			if (is_array($valor_post)) {
				$claves_comentario[$clave_post] = 'ARRAY';
			} else {
				$claves_comentario[$clave_post] = $valor_post;
			}
		}

		toba::logger()->info(
			'DIAGNOSTICO POST COMENTARIO: '
			. print_r($claves_comentario, true)
		);

		toba::logger()->info(
			'DIAGNOSTICO: INICIO evt__3_FORMULARIO_CON_TODO_modificacion'
		);

		// -------------------------------------------------------------------
		// 1. Recuperar el programa seleccionado.
		// -------------------------------------------------------------------
		$id_programa = '';

		if (isset($this->id_programa_seleccionado)) {
			$id_programa = $this->id_programa_seleccionado;
		}

		if (empty($id_programa)) {
			$id_programa = toba::memoria()->get_dato_operacion(
				'id_programa_seleccionado'
			);
		}

		if (empty($id_programa)) {
			throw new toba_error(
				'No se pudo identificar el programa seleccionado.'
			);
		}

		// -------------------------------------------------------------------
		// 2. Datos actuales del programa.
		// -------------------------------------------------------------------
		$programa = $this->dep('datos')->tabla('programas')
			->get_datos_programa_integrado($id_programa);

		if (!$programa) {
			throw new toba_error(
				'No se encontraron los datos del programa.'
			);
		}

		// -------------------------------------------------------------------
		// 3. Estado con el que se cargo la pantalla.
		// -------------------------------------------------------------------
		$estado_actual = toba::memoria()->get_dato_operacion(
			'estado_programa_al_cargar'
		);

		if (!isset($estado_actual) || is_array($estado_actual)
			|| trim($estado_actual) === '') {
			$estado_actual = isset($programa['estado'])
				? $programa['estado'] : '';
		}

		// -------------------------------------------------------------------
		// 4. Estado que se quiere aplicar.
		//    Se buscan todos los campos 'estado' de la pantalla y se toma el
		//    que difiere del estado con el que se cargo (o sea, el cambio
		//    que hizo el usuario).
		// -------------------------------------------------------------------
		$candidatos_estado = array();

		foreach ($_POST as $clave_post => $valor_post) {

			if (is_array($valor_post)) {
				continue;
			}

			// Solo campos de formularios (no filas de multilinea).
			if (strpos($clave_post, 'ef_form_') !== 0) {
				continue;
			}

			if (substr($clave_post, -6) === 'estado') {
				$candidatos_estado[] = trim($valor_post);
			}
		}

		if (isset($datos['estado']) && !is_array($datos['estado'])
			&& trim($datos['estado']) !== '') {
			$candidatos_estado[] = trim($datos['estado']);
		}

		$estado_nuevo = $estado_actual;

		foreach ($candidatos_estado as $candidato_estado) {

			if ($candidato_estado !== ''
				&& $candidato_estado !== $estado_actual) {
				$estado_nuevo = $candidato_estado;
			}
		}

		// Si no hay ningun cambio, usar el estado pendiente (si quedo de una
		// validacion anterior).
		if ($estado_nuevo === '' || $estado_nuevo === $estado_actual) {

			$estado_pendiente = toba::memoria()->get_dato_operacion(
				'estado_nuevo_pendiente'
			);

			if (isset($estado_pendiente) && !is_array($estado_pendiente)
				&& trim($estado_pendiente) !== ''
				&& trim($estado_pendiente) !== $estado_actual) {
				$estado_nuevo = trim($estado_pendiente);
			}
		}

		if ($estado_nuevo === '') {
			$estado_nuevo = $estado_actual;
		}

		// -------------------------------------------------------------------
		// 5. Comentario nuevo.
		//    Puede estar en este formulario o en otro formulario de la
		//    pantalla, por eso tambien se busca en el POST.
		// -------------------------------------------------------------------
		$comentario = '';

		if (isset($datos['comentario']) && !is_array($datos['comentario'])) {
			$comentario = trim($datos['comentario']);
		}

		if ($comentario === '') {

			foreach ($_POST as $clave_post => $valor_post) {

				if (is_array($valor_post)) {
					continue;
				}

				// Solo campos de formularios (no filas de multilinea).
				if (strpos($clave_post, 'ef_form_') !== 0) {
					continue;
				}

				// El comentario nuevo termina en 'comentario'. El historial
				// termina en 'comentarios' y no se toma como comentario.
				if (substr($clave_post, -10) === 'comentario') {

					if (trim($valor_post) !== '') {
						$comentario = trim($valor_post);
					}
				}
			}
		}

		// Recuperar el comentario pendiente de una validacion anterior.
		if ($comentario === '') {

			$comentario_pendiente = toba::memoria()->get_dato_operacion(
				'comentario_nuevo_pendiente'
			);

			if (isset($comentario_pendiente)
				&& !is_array($comentario_pendiente)) {
				$comentario = trim($comentario_pendiente);
			}
		}

		// -------------------------------------------------------------------
		// 6. Validacion al pasar a Departamento.
		//    Los multilinea se validan en el formulario principal.
		// -------------------------------------------------------------------
		if ($estado_nuevo == 'depto' && $estado_actual != 'depto') {

			$campos_obligatorios = array(
				'fundamentacion',
				'objetivos',
				'programa_analitico',
				'bibliografia',
				'propuesta_metodologica',
				'evaluacion_acreditacion',
				'distribucion_horaria',
				'horas_practicas',
				'horas_teoricas',
				'cronograma_tentativo'
			);

			// Valores de los campos obligatorios que estan en pantalla.
			$valores_pantalla = array();

			foreach ($_POST as $clave_post => $valor_post) {

				if (is_array($valor_post)) {
					continue;
				}

				// Solo campos de formularios (no filas de multilinea).
				if (strpos($clave_post, 'ef_form_') !== 0) {
					continue;
				}

				foreach ($campos_obligatorios as $campo) {

					$largo = strlen($campo);

					if (strlen($clave_post) > $largo
						&& substr($clave_post, -$largo) === $campo) {
						$valores_pantalla[$campo] = $valor_post;
					}
				}
			}

			$faltantes = array();

			foreach ($campos_obligatorios as $campo) {

				if (array_key_exists($campo, $datos)) {
					// Valor cargado en este formulario.
					$valor = $datos[$campo];
				} elseif (array_key_exists($campo, $valores_pantalla)) {
					// Valor cargado en otro formulario de la pantalla.
					$valor = $valores_pantalla[$campo];
				} else {
					// Valor ya guardado en la base.
					$valor = isset($programa[$campo])
						? $programa[$campo] : '';
				}

				if (!isset($valor) || is_array($valor)
					|| trim($valor) === '') {
					$faltantes[] = $campo;
				}
			}

			if (!empty($faltantes)) {

				// Conservar el comentario para no perderlo.
				if ($comentario !== '') {
					toba::memoria()->set_dato_operacion(
						'comentario_nuevo_pendiente',
						$comentario
					);
				}

				// Conservar el estado que se quiso aplicar.
				toba::memoria()->set_dato_operacion(
					'estado_nuevo_pendiente',
					$estado_nuevo
				);

				throw new toba_error(
					'No se puede enviar el programa a Departamento. '
					. 'Faltan completar campos obligatorios: '
					. implode(', ', $faltantes) . '.'
				);
			}
		}

		// -------------------------------------------------------------------
		// 7. Historial de comentarios.
		//    Se conserva todo el historial y se agrega el comentario nuevo.
		// -------------------------------------------------------------------
		$comentarios = isset($programa['comentarios'])
			? $programa['comentarios'] : '';

		$comentarios_a_aplicar = '';

		if ($comentario != '') {

			$fecha = new DateTime(
				'now',
				new DateTimeZone('America/Argentina/Buenos_Aires')
			);

			$timestamp = $fecha->format('Y-m-d H:i:s');

			$usuario_id = toba::usuario()->get_id();
			$nombre_completo = toba::usuario()->get_nombre();

			// Si el texto no es UTF-8 valido se convierte antes de armar el
			// HTML. Si no, htmlspecialchars devuelve una cadena vacia y el
			// comentario se guarda sin el texto.
			if (!mb_check_encoding($comentario, 'UTF-8')) {
				$comentario = mb_convert_encoding(
					$comentario,
					'UTF-8',
					'ISO-8859-1'
				);
			}

			//*****
			toba::logger()->info(
	'DIAGNOSTICO COMENTARIO: '
	. print_r(array(
		'texto' => $comentario,
		'longitud' => strlen($comentario),
		'utf8_valido' => mb_check_encoding($comentario, 'UTF-8')
	), true)
);
			//*****
			
			
			$comentario_html = nl2br(
				htmlspecialchars(
					$comentario,
					ENT_QUOTES,
					'UTF-8'
				)
			);

			$nuevo_comentario =
				"<div style='border:1px solid #ccc;padding:10px;margin-top:10px;'>"
				. "<strong>"
				. $nombre_completo
				. " ("
				. $usuario_id
				. ") - "
				. $timestamp
				. "</strong><br><p style='margin:5px 0;'>"
				. $comentario_html
				. "</p></div>";

			$comentarios .= $nuevo_comentario;

			$comentarios_a_aplicar = $comentarios;
		}

		// -------------------------------------------------------------------
		// 8. Firma al pasar a Departamento.
		// -------------------------------------------------------------------
		$firma_a_aplicar = '';

		if ($estado_nuevo == 'depto' && $estado_actual != 'depto') {

			$fecha = new DateTime(
				'now',
				new DateTimeZone('America/Argentina/Buenos_Aires')
			);

			$timestamp = $fecha->format('Y-m-d H:i:s');

			$firma_a_aplicar = 'Firmado electrónicamente por '
				. toba::usuario()->get_nombre()
				. ' - Responsable de Cátedra - '
				. $timestamp;
		}

		// -------------------------------------------------------------------
		// 9. Dejar todo preparado para el formulario principal.
		//    OJO: aca NO se escribe en la base.
		// -------------------------------------------------------------------
		toba::memoria()->set_dato_operacion(
			'estado_nuevo_a_aplicar',
			$estado_nuevo
		);

		toba::memoria()->set_dato_operacion(
			'comentarios_a_aplicar',
			$comentarios_a_aplicar
		);

		toba::memoria()->set_dato_operacion(
			'firma_a_aplicar',
			$firma_a_aplicar
		);

		toba::logger()->info(
			'DIAGNOSTICO: FIN evt__3_FORMULARIO_CON_TODO_modificacion'
		);
	}
	
	
	
/////////////////////////////////////////////////////////////////////////////////////////////    
	
	// ======================================================
// AUXILIARES PARA LA PANTALLA DE VISUALIZACION
// ======================================================

function ver_obtener_id_programa()
{
	if (!isset($this->id_programa_seleccionado)
		|| $this->id_programa_seleccionado == '') {

		$this->id_programa_seleccionado =
			toba::memoria()->get_dato_operacion(
				'id_programa_seleccionado'
			);
	}

	return $this->id_programa_seleccionado;
}


// Cargar un formulario multilinea desde un campo JSON.

function ver_cargar_multilinea($form, $columna, $campos)
{
	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos_programa = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos_programa
		|| empty($datos_programa[$columna])) {
		return;
	}

	$json_utf8 = mb_convert_encoding(
		$datos_programa[$columna],
		'UTF-8',
		'ISO-8859-1'
	);

	$filas = json_decode($json_utf8, true);

	if (!is_array($filas)) {
		return;
	}

	$datos_ml = array();

	foreach ($filas as $fila) {

		$registro = array();

		foreach ($campos as $indice => $campo) {

			$valor = isset($fila[$indice])
				? $fila[$indice] : '';

			if (is_string($valor)) {
				$valor = mb_convert_encoding(
					$valor,
					'ISO-8859-1',
					'UTF-8'
				);
			}

			$registro[$campo] = $valor;
		}

		$datos_ml[] = $registro;
	}

if (!empty($datos_ml)) {
	$form->set_datos($datos_ml);
}

// Configurar como no editables las columnas existentes.
$campos_existentes = array();

foreach ($campos as $campo) {
	if ($form->existe_ef($campo)) {
		$campos_existentes[] = $campo;
	}
}

if (!empty($campos_existentes)) {
	$form->set_solo_lectura($campos_existentes, true);
}    
	
		// Todos los campos del multilinea en solo lectura.
	// $form->set_solo_lectura($campos, true);
}




////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_form_datos_materia(
	catedras_ei_formulario $form
)
{
	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos) {
		return;
	}

	$form->set_datos($datos);

	//$form->set_solo_lectura(array_keys($datos), true);
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////
function conf__ver_formulario_con_todo(
	toba_ei_formulario $form
)
{
	echo '<link rel="stylesheet" type="text/css" href="/toba/proyectos/catedras/www/skins/custom/custom.css">';

	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos) {
		return;
	}

	// Cargar los datos del programa.
	$form->set_datos($datos);

	// Configurar todos los campos como solo lectura.
	$campos = $form->get_nombres_ef();

	if (!empty($campos)) {
		$form->set_solo_lectura($campos, true);
	}

	toba::notificacion()->agregar(
		"En esta sección usted puede ver los datos del Programa presentado.",
		'info'
	);
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_form_2(
	toba_ei_formulario $form
)
{
	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos) {
		return;
	}

	// Cargar los datos del programa.
	$form->set_datos($datos);

	// Configurar todos los campos como solo lectura.
	$campos = $form->get_nombres_ef();

	if (!empty($campos)) {
		$form->set_solo_lectura($campos, true);
	}
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_form_3(
	toba_ei_formulario $form
)
{
	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos) {
		return;
	}

	// No mostrar un comentario nuevo.
	$datos['comentario'] = '';

	// Cargar los datos del programa.
	$form->set_datos($datos);

	// Configurar todos los campos como solo lectura.
	$campos = $form->get_nombres_ef();

	if (!empty($campos)) {
		$form->set_solo_lectura($campos, true);
	}
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////    
////////////////////////////////////////////////////////////////////////////////////////////////////////////
function conf__ver_equipo_de_catedra(
	toba_ei_formulario_ml $form
)
{
	$id_programa = $this->ver_obtener_id_programa();

	if (empty($id_programa)) {
		return;
	}

	$datos_programa = $this->dep('datos')
		->tabla('programas')
		->get_datos_programa_integrado($id_programa);

	if (!$datos_programa) {
		return;
	}

	$datos_ml = array();

	// Primera fila: responsable de la cátedra.
	if (!empty($datos_programa['apellido_resp'])
		|| !empty($datos_programa['nombre_resp'])) {

		$cargo_resp = isset($datos_programa['cargo_resp'])
			? $datos_programa['cargo_resp'] : '';

		$cargo = substr($cargo_resp, 0, 3);
		$dedicacion = substr($cargo_resp, 3, 1);

		$datos_ml[] = array(
			'apellido' => mb_convert_encoding(
				$datos_programa['apellido_resp'],
				'ISO-8859-1',
				'UTF-8'
			),
			'nombre' => mb_convert_encoding(
				$datos_programa['nombre_resp'],
				'ISO-8859-1',
				'UTF-8'
			),
			'legajo' => mb_convert_encoding(
				isset($datos_programa['legajo_resp'])
					? $datos_programa['legajo_resp'] : '',
				'ISO-8859-1',
				'UTF-8'
			),
			'cargo' => mb_convert_encoding(
				$cargo,
				'ISO-8859-1',
				'UTF-8'
			),
			'dedicacion' => mb_convert_encoding(
				$dedicacion,
				'ISO-8859-1',
				'UTF-8'
			)
		);
	}

	// Integrantes guardados en equipo_catedra.
	if (!empty($datos_programa['equipo_catedra'])) {

		$json_utf8 = mb_convert_encoding(
			$datos_programa['equipo_catedra'],
			'UTF-8',
			'ISO-8859-1'
		);

		$equipo = json_decode($json_utf8, true);

		if (is_array($equipo)) {

			foreach ($equipo as $fila) {

				$datos_ml[] = array(
					'apellido' => mb_convert_encoding(
						isset($fila[0]) ? $fila[0] : '',
						'ISO-8859-1',
						'UTF-8'
					),
					'nombre' => mb_convert_encoding(
						isset($fila[1]) ? $fila[1] : '',
						'ISO-8859-1',
						'UTF-8'
					),
					'legajo' => mb_convert_encoding(
						isset($fila[2]) ? $fila[2] : '',
						'ISO-8859-1',
						'UTF-8'
					),
					'cargo' => mb_convert_encoding(
						isset($fila[3]) ? $fila[3] : '',
						'ISO-8859-1',
						'UTF-8'
					),
					'dedicacion' => mb_convert_encoding(
						isset($fila[4]) ? $fila[4] : '',
						'ISO-8859-1',
						'UTF-8'
					)
				);
			}
		}
	}

	// Cargar los datos en el formulario multilínea.
	if (!empty($datos_ml)) {
		$form->set_datos($datos_ml);
	}

	// Configurar las columnas existentes como no editables.
	$campos = array(
		'apellido',
		'nombre',
		'legajo',
		'cargo',
		'dedicacion'
	);

	$campos_existentes = array();

	foreach ($campos as $campo) {
		if ($form->existe_ef($campo)) {
			$campos_existentes[] = $campo;
		}
	}

	if (!empty($campos_existentes)) {
		$form->set_solo_lectura($campos_existentes, true);
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_dist_hor_planif(
	toba_ei_formulario_ml $form
)
{
	$this->ver_cargar_multilinea(
		$form,
		'dist_horaria_planif',
		array(
			'dia',
			'desde',
			'hasta',
			'tipo'
		)
	);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_horario_de_consulta(
	toba_ei_formulario_ml $form
)
{
	$this->ver_cargar_multilinea(
		$form,
		'horarios_consulta',
		array(
			'docente',
			'legajo',
			'dia',
			'hora',
			'lugar'
		)
	);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////

function conf__ver_otras_tareas(
	toba_ei_formulario_ml $form
)
{
	$this->ver_cargar_multilinea(
		$form,
		'otras_tareas',
		array(
			'apellido',
			'nombre',
			'actividad',
			'horas',
			'descripcion'
		)
	);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
function conf__ver_bibliografia_pedida(
	toba_ei_formulario_ml $form
)
{
	$this->ver_cargar_multilinea(
		$form,
		'bibliografia_pedida',
		array(
			'titulo',
			'autores',
			'editorial',
			'edicion',
			'biblioteca',
			'isbn'
		)
	);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	
	
	
}
?>