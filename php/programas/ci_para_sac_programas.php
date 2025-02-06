<?php
class ci_para_sac_programas extends catedras_ci
{
	//esto es de operaciones departamentos
	
	
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
	
	
	function vista_impresion(toba_impresion $salida)
{
	// Recuperar los datos del programa seleccionado desde la memoria
	$this->id_programa_seleccionado = toba::memoria()->get_dato_operacion('id_programa_seleccionado');
	$this->ano_academico_selec = toba::memoria()->get_dato_operacion('ano_academico_selec');
	$this->nombre_materia_selec = toba::memoria()->get_dato_operacion('nombre_materia_selec');
	$this->cod_guarani_selec = toba::memoria()->get_dato_operacion('cod_guarani_selec');
	$this->nombre_carrera_selec = toba::memoria()->get_dato_operacion('nombre_carrera_selec');
	$this->depto_selec = toba::memoria()->get_dato_operacion('depto_selec');
	$this->area_selec = toba::memoria()->get_dato_operacion('area_selec');
	$this->orientacion_selec = toba::memoria()->get_dato_operacion('orientacion_selec');
	$this->optativa_selec = toba::memoria()->get_dato_operacion('optativa_selec');
	$this->trayecto_selec = toba::memoria()->get_dato_operacion('trayecto_selec');
	$this->periodo_dictado_selec = toba::memoria()->get_dato_operacion('periodo_dictado_selec');
	$this->ano_plan_selec = toba::memoria()->get_dato_operacion('ano_plan_selec');
	$this->horas_totales_selec = toba::memoria()->get_dato_operacion('horas_totales_selec');
	$this->horas_semanales_selec = toba::memoria()->get_dato_operacion('horas_semanales_selec');
	$this->correlativas_para_cursar_selec = toba::memoria()->get_dato_operacion('correlativas_para_cursar_selec');
	$this->correlativas_para_aprobar_selec = toba::memoria()->get_dato_operacion('correlativas_para_aprobar_selec');
	$this->contenidos_minimos_selec = toba::memoria()->get_dato_operacion('contenidos_minimos_selec');
	$this->competencias_selec = toba::memoria()->get_dato_operacion('competencias_selec');
	$this->plan_ordenanzas_selec = toba::memoria()->get_dato_operacion('plan_ordenanzas_selec');
	$this->apellido_resp_selec = toba::memoria()->get_dato_operacion('apellido_resp_selec');
	$this->nombre_resp_selec = toba::memoria()->get_dato_operacion('nombre_resp_selec');
	$this->cargo_resp_selec = toba::memoria()->get_dato_operacion('cargo_resp_selec');
	$this->equipo_catedra_selec = toba::memoria()->get_dato_operacion('equipo_catedra_selec');
	$this->fundamentacion_selec = toba::memoria()->get_dato_operacion('fundamentacion_selec');
	$this->objetivos_selec = toba::memoria()->get_dato_operacion('objetivos_selec');
	$this->programa_analitico_selec = toba::memoria()->get_dato_operacion('programa_analitico_selec');
	$this->bibliografia_selec = toba::memoria()->get_dato_operacion('bibliografia_selec');
	$this->propuesta_metodologica_selec = toba::memoria()->get_dato_operacion('propuesta_metodologica_selec');
	$this->evaluacion_acreditacion_selec = toba::memoria()->get_dato_operacion('evaluacion_acreditacion_selec');
	$this->distribucion_horaria_selec = toba::memoria()->get_dato_operacion('distribucion_horaria_selec');
	$this->horas_teoricas_selec = toba::memoria()->get_dato_operacion('horas_teoricas_selec');
	$this->horas_practicas_selec = toba::memoria()->get_dato_operacion('horas_practicas_selec');
	$this->horas_teoricopracticas_selec = toba::memoria()->get_dato_operacion('horas_teoricopracticas_selec');
	$this->cronograma_tentativo_selec = toba::memoria()->get_dato_operacion('cronograma_tentativo_selec');
	$this->estado_selec = toba::memoria()->get_dato_operacion('estado_selec');
	$this->comentarios_selec = toba::memoria()->get_dato_operacion('comentarios_selec');
	$this->comentario_selec = toba::memoria()->get_dato_operacion('comentario_selec');
	$this->cod_carrera_selec = toba::memoria()->get_dato_operacion('cod_carrera_selec');
	
	// Verificar si el valor de id_programa_seleccionado está disponible
	if (isset($this->id_programa_seleccionado)) {
		
		// Obtener los datos del programa seleccionado
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);
		if ($datos_programa) {
			// Asignar las propiedades
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
			$this->cod_seguimiento_selec = $datos_programa['cod_seguimiento']; // Asegúrate de que este campo exista en tu tabla
	
			// Definir el encabezado con el logo
			$encabezado = '<div style="text-align: center; margin-bottom: 20px;">
							<img src="https://web.crub.uncoma.edu.ar/wp-content/uploads/2021/04/logo-unco-bariloche-azul-gris.png" alt="Logo Toba SIU" style="width: 150px;">
							</div>';
			$salida->mensaje($encabezado);
		
			// Mensaje para el nombre de la materia
			$salida->titulo('Nombre Materia: ' . $this->nombre_materia_selec);
	
			// Formato para ID del programa dentro de un cuadro
			$formato_id_programa = '
				<div style="border: 1px solid black; padding: 10px; margin: 10px 0;">
					<strong>ID Programa Seleccionado:</strong> <em>' . $this->id_programa_seleccionado . '</em>
				</div>';
			$salida->mensaje($formato_id_programa);
		
			// Construir el mensaje con formato HTML para la distribución horaria
			$formato_distribucion_horaria = '<strong>Distribución horaria:</strong><br>' . $this->distribucion_horaria_selec;
		
			// Imprimir la distribución horaria
			$salida->mensaje($formato_distribucion_horaria);
		
			// SALIDA DE TODOS LOS CAMPOS
			$salida->titulo('Año académico: ' . $this->ano_academico_selec);
			$salida->titulo('Materia: ' . $this->nombre_materia_selec);
			$salida->titulo('Cod.Guaraní: ' . $this->cod_guarani_selec);
			$salida->titulo('Carrera: ' . $this->nombre_carrera_selec);
			$salida->titulo('Departamento: ' . $this->depto_selec);
			$salida->titulo('Area: ' . $this->area_selec);
			$salida->titulo('Orientación: ' . $this->orientacion_selec);
			$salida->titulo('Optativa: ' . $this->optativa_selec);
			$salida->titulo('Trayecto: ' . $this->trayecto_selec);
			$salida->titulo('Período: ' . $this->periodo_dictado_selec);
			$salida->titulo('Año: ' . $this->ano_plan_selec);
			$salida->titulo('Horas totales: ' . $this->horas_totales_selec);
			$salida->titulo('Horas semanales: ' . $this->horas_semanales_selec);
			$salida->titulo('Correlativas para cursar:: ' . $this->correlativas_para_cursar_selec);
			$salida->titulo('Correlativas para rendir: ' . $this->correlativas_para_aprobar_selec);
			$salida->titulo('Contenidos Mínimos: ' . $this->contenidos_minimos_selec);
			$salida->titulo('Competencias: ' . $this->competencias_selec);
			$salida->titulo('Plan de Estudios Ord.: ' . $this->plan_ordenanzas_selec);
			$salida->titulo('Apellido: ' . $this->apellido_resp_selec);
			$salida->titulo('Nombre: ' . $this->nombre_resp_selec);
			$salida->titulo('Cargo: ' . $this->cargo_resp_selec);
			$salida->titulo('Equipo de cátedra: ' . $this->equipo_catedra_selec);
			$salida->titulo('Fundamentación: ' . $this->fundamentacion_selec);
			$salida->titulo('Objetivos: ' . $this->objetivos_selec);
			$salida->titulo('Programa analítico: ' . $this->programa_analitico_selec);
			$salida->titulo('Bibliografía: ' . $this->bibliografia_selec);
			$salida->titulo('Propuesta Metodológica: ' . $this->propuesta_metodologica_selec);
			$salida->titulo('Evaluación y acreditación: ' . $this->evaluacion_acreditacion_selec);
			$salida->titulo('Distribución Horaria: ' . $this->distribucion_horaria_selec);
			$salida->titulo('Horas teóricas: ' . $this->horas_teoricas_selec);
			$salida->titulo('Horas prácticas: ' . $this->horas_practicas_selec);
			$salida->titulo('Horas teorico-prácticas: ' . $this->horas_teoricopracticas_selec);
			$salida->titulo('Cronograma tentativo: ' . $this->cronograma_tentativo_selec);
			$salida->titulo('Estado: ' . $this->estado_selec);
			$salida->titulo('Comentarios: ' . $this->comentarios_selec);
			$salida->titulo('Comentario: ' . $this->comentario_selec);
			$salida->titulo('Codigo carrera: ' . $this->cod_carrera_selec);
		
			// Definir el pie de página con el código de seguimiento
			$cod_seguimiento = $this->cod_seguimiento_selec; // Asegúrate de que este campo exista y esté asignado
			$pie_pagina = '<div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px;">
							<strong>Código de Seguimiento: ' . htmlspecialchars($cod_seguimiento) . '</strong>
							</div>';
			$salida->mensaje($pie_pagina);
		} else {
			$salida->titulo('No se ha seleccionado un programa.');
		}}
}
	
	
	function vista_impresion_original(toba_impresion $salida)
{
	// Recuperar los datos del programa seleccionado desde la memoria
	$this->id_programa_seleccionado = toba::memoria()->get_dato_operacion('id_programa_seleccionado');
	$this->ano_academico_selec = toba::memoria()->get_dato_operacion('ano_academico_selec');
	$this->nombre_materia_selec = toba::memoria()->get_dato_operacion('nombre_materia_selec');
	$this->cod_guarani_selec = toba::memoria()->get_dato_operacion('cod_guarani_selec');
	$this->nombre_carrera_selec = toba::memoria()->get_dato_operacion('nombre_carrera_selec');
	$this->depto_selec = toba::memoria()->get_dato_operacion('depto_selec');
	$this->area_selec = toba::memoria()->get_dato_operacion('area_selec');
	$this->orientacion_selec = toba::memoria()->get_dato_operacion('orientacion_selec');
	$this->optativa_selec = toba::memoria()->get_dato_operacion('optativa_selec');
	$this->trayecto_selec = toba::memoria()->get_dato_operacion('trayecto_selec');
	$this->periodo_dictado_selec = toba::memoria()->get_dato_operacion('periodo_dictado_selec');
	$this->ano_plan_selec = toba::memoria()->get_dato_operacion('ano_plan_selec');
	$this->horas_totales_selec = toba::memoria()->get_dato_operacion('horas_totales_selec');
	$this->horas_semanales_selec = toba::memoria()->get_dato_operacion('horas_semanales_selec');
	$this->correlativas_para_cursar_selec = toba::memoria()->get_dato_operacion('correlativas_para_cursar_selec');
	$this->correlativas_para_aprobar_selec = toba::memoria()->get_dato_operacion('correlativas_para_aprobar_selec');
	$this->contenidos_minimos_selec = toba::memoria()->get_dato_operacion('contenidos_minimos_selec');
	$this->competencias_selec = toba::memoria()->get_dato_operacion('competencias_selec');
	$this->plan_ordenanzas_selec = toba::memoria()->get_dato_operacion('plan_ordenanzas_selec');
	$this->apellido_resp_selec = toba::memoria()->get_dato_operacion('apellido_resp_selec');
	$this->nombre_resp_selec = toba::memoria()->get_dato_operacion('nombre_resp_selec');
	$this->cargo_resp_selec = toba::memoria()->get_dato_operacion('cargo_resp_selec');
	$this->equipo_catedra_selec = toba::memoria()->get_dato_operacion('equipo_catedra_selec');
	$this->fundamentacion_selec = toba::memoria()->get_dato_operacion('fundamentacion_selec');
	$this->objetivos_selec = toba::memoria()->get_dato_operacion('objetivos_selec');
	$this->programa_analitico_selec = toba::memoria()->get_dato_operacion('programa_analitico_selec');
	$this->bibliografia_selec = toba::memoria()->get_dato_operacion('bibliografia_selec');
	$this->propuesta_metodologica_selec = toba::memoria()->get_dato_operacion('propuesta_metodologica_selec');
	$this->evaluacion_acreditacion_selec = toba::memoria()->get_dato_operacion('evaluacion_acreditacion_selec');
	$this->distribucion_horaria_selec = toba::memoria()->get_dato_operacion('distribucion_horaria_selec');
	$this->horas_teoricas_selec = toba::memoria()->get_dato_operacion('horas_teoricas_selec');
	$this->horas_practicas_selec = toba::memoria()->get_dato_operacion('horas_practicas_selec');
	$this->horas_teoricopracticas_selec = toba::memoria()->get_dato_operacion('horas_teoricopracticas_selec');
	$this->cronograma_tentativo_selec = toba::memoria()->get_dato_operacion('cronograma_tentativo_selec');
	$this->estado_selec = toba::memoria()->get_dato_operacion('estado_selec');
	$this->comentarios_selec = toba::memoria()->get_dato_operacion('comentarios_selec');
	$this->comentario_selec = toba::memoria()->get_dato_operacion('comentario_selec');
	$this->cod_carrera_selec = toba::memoria()->get_dato_operacion('cod_carrera_selec');
	
	// Verificar si el valor de id_programa_seleccionado está disponible
	if (isset($this->id_programa_seleccionado)) {
		
			// Mensaje para el nombre de la materia
		$salida->titulo('Nombre Materia: ' . $this->nombre_materia_selec);

		
		// Formato para ID del programa dentro de un cuadro
		$formato_id_programa = '
			<div style="border: 1px solid black; padding: 10px; margin: 10px 0;">
				<strong>ID Programa Seleccionado:</strong> <em>' . $this->id_programa_seleccionado . '</em>
			</div>';
		$salida->mensaje($formato_id_programa);
	
		// Construir el mensaje con formato HTML para la distribución horaria
		$formato_distribucion_horaria = '<strong>Distribución horaria:</strong><br>' . $this->distribucion_horaria_selec;
					
		$imagen = toba::proyecto()->get_path().'/www/img/logo_toba_siu.jpg';
		$encabezado = '<img src="https://web.crub.uncoma.edu.ar/wp-content/uploads/2021/04/logo-unco-bariloche-azul-gris.png" alt="Logo Toba SIU">';

		
		// Imprimir la distribución horaria
		$salida->mensaje($formato_distribucion_horaria . $encabezado) ;
		
		// SALIDA DE TODOS LOS CAMPOS
		$salida->titulo('Año académico: ' . $this->ano_academico_selec);
		$salida->titulo('Materia: ' . $this->nombre_materia_selec);
		$salida->titulo('Cod.Guaraní: ' . $this->cod_guarani_selec);
		$salida->titulo('Carrera: ' . $this->nombre_carrera_selec);
		$salida->titulo('Departamento: ' . $this->depto_selec);
		$salida->titulo('Area: ' . $this->area_selec);
		$salida->titulo('Orientación: ' . $this->orientacion_selec);
		$salida->titulo('Optativa: ' . $this->optativa_selec);
		$salida->titulo('Trayecto: ' . $this->trayecto_selec);
		$salida->titulo('Período: ' . $this->periodo_dictado_selec);
		$salida->titulo('Año: ' . $this->ano_plan_selec);
		$salida->titulo('Horas totales: ' . $this->horas_totales_selec);
		$salida->titulo('Horas semanales: ' . $this->horas_semanales_selec);
		$salida->titulo('Correlativas para cursar:: ' . $this->correlativas_para_cursar_selec);
		$salida->titulo('Correlativas para rendir: ' . $this->correlativas_para_aprobar_selec);
		$salida->titulo('Contenidos Mínimos: ' . $this->contenidos_minimos_selec);
		$salida->titulo('Competencias: ' . $this->competencias_selec);
		$salida->titulo('Plan de Estudios Ord.: ' . $this->plan_ordenanzas_selec);
		$salida->titulo('Apellido: ' . $this->apellido_resp_selec);
		$salida->titulo('Nombre: ' . $this->nombre_resp_selec);
		$salida->titulo('Cargo: ' . $this->cargo_resp_selec);
		$salida->titulo('Equipo de cátedra: ' . $this->equipo_catedra_selec);
		$salida->titulo('Fundamentación: ' . $this->fundamentacion_selec);
		$salida->titulo('Objetivos: ' . $this->objetivos_selec);
		$salida->titulo('Programa analítico: ' . $this->programa_analitico_selec);
		$salida->titulo('Bibliografía: ' . $this->bibliografia_selec);
		$salida->titulo('Propuesta Metodológica: ' . $this->propuesta_metodologica_selec);
		$salida->titulo('Evaluación y acreditación: ' . $this->evaluacion_acreditacion_selec);
		$salida->titulo('Distribución Horaria: ' . $this->distribucion_horaria_selec);
		$salida->titulo('Horas teóricas: ' . $this->horas_teoricas_selec);
		$salida->titulo('Horas prácticas: ' . $this->horas_practicas_selec);
		$salida->titulo('Horas teorico-prácticas: ' . $this->horas_teoricopracticas_selec);
		$salida->titulo('Cronograma tentativo: ' . $this->cronograma_tentativo_selec);
		$salida->titulo('Estado: ' . $this->estado_selec);
		$salida->titulo('Comentarios: ' . $this->comentarios_selec);
		$salida->titulo('Comentario: ' . $this->comentario_selec);
		$salida->titulo('Codigo carrera: ' . $this->cod_carrera_selec);
		


		// Obtener los datos del programa seleccionado
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);
		// Aquí podrías imprimir otros datos de $datos_programa si es necesario
	} else {
		$salida->titulo('No se ha seleccionado un programa.');
	}
}

	
	
	
	
	function conf__formulario_con_todo(toba_ei_formulario $form)
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
			
$this->firma_doc_selec = $datos_programa['firma_doc'];
$this->firma_dto_selec = $datos_programa['firma_dto'];
$this->firma_sac_selec = $datos_programa['firma_sac'];
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
toba::memoria()->set_dato_operacion('firma_doc_selec', $this->firma_doc_selec);
toba::memoria()->set_dato_operacion('firma_dto_selec', $this->firma_dto_selec);
toba::memoria()->set_dato_operacion('firma_sac_selec', $this->firma_sac_selec);              
			
			// Establecer los datos en el formulario
			$form->set_datos($datos_programa);

			// Setear de solo lectura para 'contenidos_minimos'
			$form->set_solo_lectura(array('contenidos_minimos'), true);
		}
	} else {
		// Si no hay datos cargados, eliminar el evento de eliminar
		$this->pantalla()->eliminar_evento('eliminar');
	}
}

	
function evt__formulario_con_todo__modificacion($datos) {

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
		$comentarios = $this->dep('datos')->tabla('programas')->get_columna('comentarios');

		// Concatenar el nuevo comentario al contenido existente
		$nuevo_comentarios = $comentarios . $nuevo_comentario;

		// Asignar el nuevo valor a la columna comentarios
		$datos['comentarios'] = $nuevo_comentarios;
	}

	// *** Lógica para actualizar firma_dto y firma_sac según el estado ***
	if (isset($datos['estado'])) {
		// Obtener el estado actual
		$estado_nuevo = $datos['estado'];

		if ($estado_nuevo === 'depto') {
			// Si el estado cambia a 'depto', eliminar el contenido de 'firma_dto'
			$datos['firma_dto'] = '';
		} elseif ($estado_nuevo === 'aprobado') {
			// Si el estado cambia a 'aprobado', actualizar 'firma_sac' con la firma electrónica

			// Obtener el nombre completo del usuario SAC
			$nombre_completo = toba::usuario()->get_nombre();

			// Crear un objeto DateTime con la zona horaria de Argentina
			$timestamp_obj = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
			$formatted_timestamp = $timestamp_obj->format('Y-m-d H:i:s');

			// Crear el contenido para 'firma_sac'
			$datos['firma_sac'] = "Firmado electrónicamente por $nombre_completo - Secretaría Académica - $formatted_timestamp";
		}
	}
	// *** Fin de la lógica para actualizar firma_dto y firma_sac ***

	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar al guardar correctamente
	toba::notificacion()->agregar("Su programa ha sido guardado correctamente", 'info');

	// Sincronizar los datos con la base de datos
	$this->dep('datos')->tabla('programas')->sincronizar();
}
	


function evt__formulario_con_todo__modificacion_original($datos) {


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
		$comentarios = $this->dep('datos')->tabla('programas')->get_columna('comentarios');

		// Concatenar el nuevo comentario al contenido existente
		$nuevo_comentarios = $comentarios . $nuevo_comentario;

		// Asignar el nuevo valor a la columna comentarios
		$datos['comentarios'] = $nuevo_comentarios;
	}

	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar al guardar correctamente
	toba::notificacion()->agregar("Su programa ha sido guardado correctamente", 'info');

	// Refrescar el formulario para mostrar el nuevo subtotal
	$this->dep('datos')->tabla('programas')->sincronizar();
}

	
	
	
	//---- CUADROS
	
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_sac();
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_filtrado_sac($usuario_id, $nombre_usuario);
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

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_aprobado();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_aprobado($usuario_id, $nombre_usuario);
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

	// ************ SEGUNDA PANTALLA ************************
	
//-----------------------------------------------------------------------------------------------    
	
	
	
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
	



	//-----------------------------------------------------------------------------------
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
		";
	}

	

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------


}
?>