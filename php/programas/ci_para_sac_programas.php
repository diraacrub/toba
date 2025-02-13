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
	$this->id_programa_seleccionado       = toba::memoria()->get_dato_operacion('id_programa_seleccionado');
	$this->ano_academico_selec            = toba::memoria()->get_dato_operacion('ano_academico_selec');
	$this->nombre_materia_selec           = toba::memoria()->get_dato_operacion('nombre_materia_selec');
	$this->cod_guarani_selec              = toba::memoria()->get_dato_operacion('cod_guarani_selec');
	$this->nombre_carrera_selec           = toba::memoria()->get_dato_operacion('nombre_carrera_selec');
	$this->depto_selec                    = toba::memoria()->get_dato_operacion('depto_selec');
	$this->area_selec                     = toba::memoria()->get_dato_operacion('area_selec');
	$this->orientacion_selec              = toba::memoria()->get_dato_operacion('orientacion_selec');
	$this->optativa_selec                 = toba::memoria()->get_dato_operacion('optativa_selec');
	$this->trayecto_selec                 = toba::memoria()->get_dato_operacion('trayecto_selec');
	$this->periodo_dictado_selec          = toba::memoria()->get_dato_operacion('periodo_dictado_selec');
	$this->ano_plan_selec                 = toba::memoria()->get_dato_operacion('ano_plan_selec');
	$this->horas_totales_selec            = toba::memoria()->get_dato_operacion('horas_totales_selec');
	$this->horas_semanales_selec          = toba::memoria()->get_dato_operacion('horas_semanales_selec');
	$this->correlativas_para_cursar_selec = toba::memoria()->get_dato_operacion('correlativas_para_cursar_selec');
	$this->correlativas_para_aprobar_selec= toba::memoria()->get_dato_operacion('correlativas_para_aprobar_selec');
	$this->contenidos_minimos_selec       = toba::memoria()->get_dato_operacion('contenidos_minimos_selec');
	$this->competencias_selec             = toba::memoria()->get_dato_operacion('competencias_selec');
	$this->plan_ordenanzas_selec          = toba::memoria()->get_dato_operacion('plan_ordenanzas_selec');
	$this->apellido_resp_selec            = toba::memoria()->get_dato_operacion('apellido_resp_selec');
	$this->nombre_resp_selec              = toba::memoria()->get_dato_operacion('nombre_resp_selec');
	$this->cargo_resp_selec               = toba::memoria()->get_dato_operacion('cargo_resp_selec');
	$this->equipo_catedra_selec           = toba::memoria()->get_dato_operacion('equipo_catedra_selec');
	$this->fundamentacion_selec           = toba::memoria()->get_dato_operacion('fundamentacion_selec');
	$this->objetivos_selec                = toba::memoria()->get_dato_operacion('objetivos_selec');
	$this->programa_analitico_selec       = toba::memoria()->get_dato_operacion('programa_analitico_selec');
	$this->bibliografia_selec             = toba::memoria()->get_dato_operacion('bibliografia_selec');
	$this->propuesta_metodologica_selec   = toba::memoria()->get_dato_operacion('propuesta_metodologica_selec');
	$this->evaluacion_acreditacion_selec  = toba::memoria()->get_dato_operacion('evaluacion_acreditacion_selec');
	$this->distribucion_horaria_selec     = toba::memoria()->get_dato_operacion('distribucion_horaria_selec');
	$this->horas_teoricas_selec           = toba::memoria()->get_dato_operacion('horas_teoricas_selec');
	$this->horas_practicas_selec          = toba::memoria()->get_dato_operacion('horas_practicas_selec');
	$this->horas_teoricopracticas_selec   = toba::memoria()->get_dato_operacion('horas_teoricopracticas_selec');
	$this->cronograma_tentativo_selec     = toba::memoria()->get_dato_operacion('cronograma_tentativo_selec');
	$this->estado_selec                   = toba::memoria()->get_dato_operacion('estado_selec');
	$this->comentarios_selec              = toba::memoria()->get_dato_operacion('comentarios_selec');
	$this->comentario_selec               = toba::memoria()->get_dato_operacion('comentario_selec');
	$this->cod_carrera_selec              = toba::memoria()->get_dato_operacion('cod_carrera_selec');
	$this->firma_doc_selec                = toba::memoria()->get_dato_operacion('firma_doc_selec');
	$this->firma_dto_selec                = toba::memoria()->get_dato_operacion('firma_dto_selec');
	$this->firma_sac_selec                = toba::memoria()->get_dato_operacion('firma_sac_selec');
	
	// Verificar si se seleccionó un programa
	if (isset($this->id_programa_seleccionado)) {

		// Construir el nombre que se usará como título
		$nombre_archivo = $this->ano_academico_selec . "_" .
							$this->nombre_materia_selec . "_" .
							$this->nombre_carrera_selec . "_CRUB-UNCo";

		// Comenzar el documento HTML incluyendo la cabecera con el <title>
		$salida->mensaje('<!DOCTYPE html>');
		$salida->mensaje('<html>');
		$salida->mensaje('<head>');
		$salida->mensaje('<meta charset="utf-8">');
		$salida->mensaje('<title>' . $nombre_archivo . '</title>');
		// Inyectar estilos para impresión
$salida->mensaje('<style>
	@page {
		margin: 0;
	}
	body {
		margin: 0;
		padding: 0;
	}
	/* Container to adjust overall positioning for print */
	.print-container {
		margin-top: -250px; /* tweak this value if needed */
	}
	table {
		width: 100%;
		border-collapse: collapse;
		border-spacing: 0;
	}
	@media print {
		thead {
			display: table-header-group;
			margin: none;
			padding: none;

		}
		thead tr,
		thead td,
		thead th {
			margin: none;
			padding: none;

		}
		thead img {
			display: block; /* eliminate inline image spacing */
			margin: 0;
			padding: 0;
		}
		tfoot {
			display: table-footer-group;
		}
	}
</style>');

// Wrap the table in a container that applies the negative top margin
$salida->mensaje('<div class="print-container"><table>');
	// Membrete in <thead>
	$salida->mensaje('<thead>');
		$salida->mensaje('<tr>');
			$salida->mensaje('<td style="text-align: left; padding: 0; margin: 0;">');
				$salida->mensaje('<img src= "https://web.crub.uncoma.edu.ar/wp-content/uploads/2021/04/logo-unco-bariloche-azul-gris.png" alt="Logo UNCo" style="width: 150px; height: auto; margin: 0;">');
			$salida->mensaje('</td>');
		$salida->mensaje('</tr>');
	$salida->mensaje('</thead>');
		
		// Contenido (tbody)
		$salida->mensaje('<tbody>');
		$salida->mensaje('<tr><td>');
		
		// Variables para facilitar la escritura de estilos en los DIV
		$abre_div_style_1 = '<div style="text-align: justify; font-size: 15px; font-family: \'Times New Roman\';">';
		$cierra_div_style_1 = '</div>';
		$abre_div_style_2 = '<div style="text-align: justify; margin-left: 20px;">';
		$cierra_div_style_2 = '</div>';
		
		// SALIDA DE LOS CAMPOS
		$salida->mensaje('<div style="text-align: center; font-size: 15px; font-family: \'Times New Roman\';">
			AÑO ACADÉMICO: <strong>' . $this->ano_academico_selec . '</strong>
		</div><br><br>');
		$salida->mensaje('<div style="font-size: 15px; font-family: \'Times New Roman\';">
			DEPARTAMENTO: ' . $this->depto_selec . '
		</div>');
		$salida->mensaje('<div style="font-size: 15px; font-family: \'Times New Roman\';">
			PROGRAMA DE CÁTEDRA: <strong>' . $this->nombre_materia_selec . '</strong><br>
			(Cod.Guaraní: ' . $this->cod_guarani_selec . ')
		</div>');
		$salida->mensaje($abre_div_style_1 . 'OPTATIVA: ' . $this->optativa_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARRERA A LA QUE PERTENECE Y/O SE OFRECE: <br>' .
			$abre_div_style_2 . '<strong>' . $this->nombre_carrera_selec . '</strong> - (' .
			$this->cod_carrera_selec . ')' . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'ÁREA: ' . $this->area_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'ORIENTACIÓN: ' . $this->orientacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'PLAN DE ESTUDIOS ORD.: ' . $this->plan_ordenanzas_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'TRAYECTO (PEF): ' . $this->trayecto_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARGA HORARIA SEMANAL SEGÚN PLAN DE ESTUDIOS: ' . $this->horas_semanales_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARGA HORARIA TOTAL: ' . $this->horas_totales_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'RÉGIMEN: ' . $this->periodo_dictado_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'EQUIPO DE CÁTEDRA: <br>' .
			$abre_div_style_2 . '<strong>' . $this->apellido_resp_selec . ', ' . $this->nombre_resp_selec .
			' - ' . $this->cargo_resp_selec . '</strong><br>' .
			nl2br($this->equipo_catedra_selec) . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'ASIGNATURAS CORRELATIVAS (según plan de estudios): ' .
			$abre_div_style_2 .
			'- PARA CURSAR: <br>' . nl2br($this->correlativas_para_cursar_selec) . '<br><br>' .
			'- PARA RENDIR EXAMEN FINAL: <br>' . nl2br($this->correlativas_para_aprobar_selec) . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>1. FUNDAMENTACIÓN:</strong> <br>' . $this->fundamentacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>2. OJETIVOS:</strong> <br>' . $this->objetivos_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>3. CONTENIDOS SEGÚN PLAN DE ESTUDIOS:</strong> <br>' . nl2br($this->contenidos_minimos_selec) . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>4. CONTENIDO PROGRAMA ANALÍTICO:</strong> <br>' . $this->programa_analitico_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>5. BIBLIOGRAFÍA BÁSICA YA DE CONSULTA:</strong> <br>' . $this->bibliografia_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>6. PROPUESTA METODOLÓGICA MODALIDAD PRESENCIAL:</strong> <br>' . $this->propuesta_metodologica_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>7. EVALUACIÓN Y CONDICIONES DE ACREDITACIÓN:</strong> <br>' . $this->evaluacion_acreditacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>8. DISTRIBUCIÓN HORARIA:</strong> <br>' . $abre_div_style_2 .
			'Horas teóricas: ' . $this->horas_teoricas_selec . '<br>' .
			'Horas prácticas: ' . $this->horas_practicas_selec . '<br>' .
			'Horas teorico-prácticas: (solo para LENB y LBIB)' . $this->horas_teoricopracticas_selec . $cierra_div_style_2 . '<br>' .
			$this->distribucion_horaria_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>9. CRONOGRAMA TENTATIVO:</strong> <br>' . $this->cronograma_tentativo_selec . $cierra_div_style_1);
		$salida->mensaje('<br><br><br><br>');
		
		$salida->mensaje('</td></tr>');
		$salida->mensaje('</tbody>');
		

		// Footer (tfoot)
		$salida->mensaje('<tfoot>');
		$salida->mensaje('<tr>');
			// Celda de la izquierda con el texto
			$salida->mensaje('<td style="text-align: left; padding: 0; margin: 0;">');
			$salida->mensaje(
				$this->firma_doc_selec . '<br>' .
				$this->firma_dto_selec . '<br>' .
				'<span style="font-size:8px;">Estado: ' . $this->estado_selec . '  - <strong>FALTAN FIRMAS </strong> </span><br>' .
				'<span style="font-size:8px;">Número de Identificacion en <a href="https://huayca.crub.uncoma.edu.ar/catedras/1.0/">huayca.crub.uncoma.edu.ar</a> ' . $this->id_programa_seleccionado . '</span>'
			);                
			
			$salida->mensaje('</td>');
			// Celda de la derecha con la imagen
			$salida->mensaje('<td style="text-align: right; padding: 0; margin: 0;">');
			$salida->mensaje('<img src="/catedras/1.0/img/logo.gif" style="max-height: 20px;">'); // Ajusta el tamaño de la imagen según sea necesario
			$salida->mensaje('</td>');
			$salida->mensaje('</tr>');
			$salida->mensaje('</tfoot>');
		//fin del footer
		
		
		// Close table
		$salida->mensaje('</table>');
		$salida->mensaje('</body>');
		$salida->mensaje('</html>');
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
	
	echo "<script type='text/javascript'>
	document.addEventListener('DOMContentLoaded', function() {
		setTimeout(function(){
			window.scrollTo(0, 0);
			console.log('works!');
		}, 1000); // Delay in milliseconds
	});
</script>";
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
	
	//---- Cuadro PENDIENTES DE APROBACIÓN SAC-----------------------------------------------------------------------

function conf__cuadro(toba_ei_cuadro $cuadro)
{
	$usuario_id    = toba::usuario()->get_id();
	$nombre_usuario= toba::usuario()->get_nombre();
	$perfil_usuario= toba::usuario()->get_perfiles_funcionales();

	// --- Determinar la lista de deptos_principales en función del perfil ---
	$deptos_principales = array(); // inicializa la lista

	// Si el array de perfiles funcionales contiene 'sac'
	if (in_array('saccrub', $perfil_usuario)) {
		// Por ejemplo, para 'sac' la lista podría ser:
		$deptos_principales = array('ECOLOGÍA', 'MATEMÁTICA', 'FÍSICA', 'QUÍMICA', 'BIOLOGÍA GENERAL','BOTÁNICA','EDUCACIÓN FÍSICA', 'DIDÁCTICA', 'ESTADÍSTICA','ENFERMERÍA','INGENIERÍA CIVIL','POLÍTICA EDUCACIONAL','PSICOLOGÍA','GEOLOGÍA Y PETRÓLEO','EXPLOTACIÓN DE RECURSOS ACUÁTICOS', 'ZOOLOGÍA');
	}
	// Si el array de perfiles funcionales contiene 'sacfale'
	elseif (in_array('sacfadel', $perfil_usuario)) {
		// Para 'sacfale' la lista es otra:
		$deptos_principales = array('IDIOMAS EXTRANJEROS CON PROPÓSITOS ESPECÍFICOS');
	}
	// Si necesitas contemplar otros casos, agrégalos aquí.

	// --- Determinar si el usuario es una excepción (sin filtro) ---
	$excepciones = array('toba', 'vero', 'nacho');
	if (in_array($usuario_id, $excepciones)) {
		$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_sac();
	} else {
		// Pasamos la lista de deptos_principales a la consulta filtrada
		$datos = $this->dep('datos')->tabla('programas')->get_listado_filtrado_sac($usuario_id, $deptos_principales);
	}

	// Ajustar el texto del campo 'estado' según corresponda
	foreach ($datos as $key => $registro) {
		if ($registro['estado'] === 'docente') {
			$datos[$key]['estado'] = 'Borrador';
		}
		if ($registro['estado'] === 'depto') {
			$datos[$key]['estado'] = 'En revisión del Departamento';
		}
		if ($registro['estado'] === 'sac') {
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
	$usuario_id         = toba::usuario()->get_id();
	$nombre_usuario     = toba::usuario()->get_nombre();
	$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();

	// --- Determinar la lista de deptos_principales en función del perfil ---
	$deptos_principales = array(); // inicializa la lista

	// Si el array de perfiles funcionales contiene 'saccrub'
	if (in_array('saccrub', $perfiles_funcionales)) {
		// Para 'saccrub' la lista podría ser:
		$deptos_principales = array('ECOLOGÍA', 'MATEMÁTICA', 'FÍSICA', 'QUÍMICA', 'BIOLOGÍA GENERAL','BOTÁNICA','EDUCACIÓN FÍSICA', 'DIDÁCTICA', 'ESTADÍSTICA','ENFERMERÍA','INGENIERÍA CIVIL','POLÍTICA EDUCACIONAL','PSICOLOGÍA','GEOLOGÍA Y PETRÓLEO','EXPLOTACIÓN DE RECURSOS ACUÁTICOS', 'ZOOLOGÍA');
	}
	// Si el array de perfiles funcionales contiene 'sacfale'
	elseif (in_array('sacfale', $perfiles_funcionales)) {
		// Para 'sacfale' la lista es:
		$deptos_principales = array('IDIOMAS EXTRANJEROS CON PROPÓSITOS ESPECÍFICOS');
	}
	// Si se requieren otros casos, agrégalos aquí.

	// --- Lista de usuarios que no requieren el filtro ---
	$excepciones = array('toba', 'vero', 'nacho');

	if (in_array($usuario_id, $excepciones)) {
		// Si el usuario es una excepción, se obtienen todos los datos
		$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_aprobado();
	} else {
		// Si el usuario no es excepción, se aplica el filtro por usuario y departamentos principales.
		// Se asume que existe un método de consulta similar al get_listado_filtrado_sac(), por ejemplo:
		$datos = $this->dep('datos')->tabla('programas')->get_listado_enviados_sac($usuario_id, $deptos_principales);
	}

	// --- Ajustar el texto del campo 'estado' según corresponda ---
	foreach ($datos as $key => $registro) {
		if ($registro['estado'] === 'docente') {
			$datos[$key]['estado'] = 'Borrador';
		}
		if ($registro['estado'] === 'depto') {
			$datos[$key]['estado'] = 'En revisión del Departamento';
		}
		if ($registro['estado'] === 'sac') {
			$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';
		}
	}

	$cuadro->set_datos($datos);
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