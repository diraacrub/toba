<?php
class ci_imprimir_programas extends catedras_ci
{
	protected $s__filtro;
	
	private $id_programa_seleccionado;

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
	// Verificar si los datos est�n cargados
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

			// Definir carreras que no muestran horas te�ricas pr�cticas
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			
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

//---- impresion
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
	
	// Verificar si se seleccion� un programa
	if (isset($this->id_programa_seleccionado)) {

		// Construir el nombre que se usar� como t�tulo
		$nombre_archivo = $this->ano_academico_selec . "_" .
							$this->nombre_materia_selec . "_" .
							$this->nombre_carrera_selec . "_CRUB-UNCo";

		// Comenzar el documento HTML incluyendo la cabecera con el <title>
		$salida->mensaje('<!DOCTYPE html>');
		$salida->mensaje('<html>');
		$salida->mensaje('<head>');
		$salida->mensaje('<meta charset="utf-8">');
		$salida->mensaje('<title>' . $nombre_archivo . '</title>');
		// Inyectar estilos para impresi�n
$salida->mensaje('<style>
	@page {
			margin: 50px
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
		$salida->mensaje('<tr><td colspan="2">');
		
		// Variables para facilitar la escritura de estilos en los DIV
		$abre_div_style_1 = '<div style="text-align: justify; font-size: 15px; font-family: \'Times New Roman\';">';
		$cierra_div_style_1 = '</div>';
		$abre_div_style_2 = '<div style="text-align: justify; margin-left: 20px;">';
		$cierra_div_style_2 = '</div>';
		
		// SALIDA DE LOS CAMPOS
		$salida->mensaje('<div style="text-align: center; font-size: 15px; font-family: \'Times New Roman\';">
			A�O ACAD�MICO: <strong>' . $this->ano_academico_selec . '</strong>
		</div><br><br>');
		$salida->mensaje('<div style="font-size: 15px; font-family: \'Times New Roman\';">
			DEPARTAMENTO: ' . $this->depto_selec . '
		</div>');
		$salida->mensaje('<div style="font-size: 15px; font-family: \'Times New Roman\';">
			PROGRAMA DE C�TEDRA: <strong>' . $this->nombre_materia_selec . '</strong><br>
			(Cod.Guaran�: ' . $this->cod_guarani_selec . ')
		</div>');
		$salida->mensaje($abre_div_style_1 . 'OPTATIVA: ' . $this->optativa_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARRERA A LA QUE PERTENECE Y/O SE OFRECE: <br>' .
			$abre_div_style_2 . '<strong>' . $this->nombre_carrera_selec . '</strong> - (' .
			$this->cod_carrera_selec . ')' . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '�REA: ' . $this->area_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'ORIENTACI�N: ' . $this->orientacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'PLAN DE ESTUDIOS ORD.: ' . $this->plan_ordenanzas_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'TRAYECTO (PEF): ' . $this->trayecto_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARGA HORARIA SEMANAL SEG�N PLAN DE ESTUDIOS: ' . $this->horas_semanales_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'CARGA HORARIA TOTAL: ' . $this->horas_totales_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'R�GIMEN: ' . $this->periodo_dictado_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'EQUIPO DE C�TEDRA: <br>' .
			$abre_div_style_2 . '<strong>' . $this->apellido_resp_selec . ', ' . $this->nombre_resp_selec .
			' - ' . $this->cargo_resp_selec . '</strong><br>' .
			nl2br($this->equipo_catedra_selec) . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . 'ASIGNATURAS CORRELATIVAS (seg�n plan de estudios): ' .
			$abre_div_style_2 .
			'- PARA CURSAR: <br>' . nl2br($this->correlativas_para_cursar_selec) . '<br><br>' .
			'- PARA RENDIR EXAMEN FINAL: <br>' . nl2br($this->correlativas_para_aprobar_selec) . $cierra_div_style_2 . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>1. FUNDAMENTACI�N:</strong> <br>' . $this->fundamentacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>2. OJETIVOS:</strong> <br>' . $this->objetivos_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>3. CONTENIDOS SEG�N PLAN DE ESTUDIOS:</strong> <br>' . nl2br($this->contenidos_minimos_selec) . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>4. CONTENIDO PROGRAMA ANAL�TICO:</strong> <br>' . $this->programa_analitico_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>5. BIBLIOGRAF�A B�SICA YA DE CONSULTA:</strong> <br>' . $this->bibliografia_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>6. PROPUESTA METODOL�GICA MODALIDAD PRESENCIAL:</strong> <br>' . $this->propuesta_metodologica_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>7. EVALUACI�N Y CONDICIONES DE ACREDITACI�N:</strong> <br>' . $this->evaluacion_acreditacion_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>8. DISTRIBUCI�N HORARIA:</strong> <br>' . $abre_div_style_2 .
			'Horas te�ricas: ' . $this->horas_teoricas_selec . '<br>' .
			'Horas pr�cticas: ' . $this->horas_practicas_selec . '<br>' .
			'Horas teorico-pr�cticas: (solo para LENB y LBIB)' . $this->horas_teoricopracticas_selec . $cierra_div_style_2 . '<br>' .
			$this->distribucion_horaria_selec . $cierra_div_style_1);
		$salida->mensaje($abre_div_style_1 . '<strong>9. CRONOGRAMA TENTATIVO:</strong> <br>' . $this->cronograma_tentativo_selec . $cierra_div_style_1);
		$salida->mensaje('<br><br><br><br>');
		
		$salida->mensaje('</td></tr>');
		$salida->mensaje('</tbody>');

		

		// Footer (tfoot)
		$salida->mensaje('<tfoot>');
		$salida->mensaje('<tr>');
			// Celda de la izquierda con el texto
// Celda de la izquierda con el texto
$salida->mensaje('<td style="text-align: left; padding: 0; margin: 0;">');
$salida->mensaje(
	'<span style="font-size:8px;"><strong> ' . strtoupper($this->estado_selec) . '</strong></span><br>' .
	'<span style="font-size:6px;">' . $this->firma_doc_selec . '</span><br>' .
	'<span style="font-size:6px;">' . $this->firma_dto_selec . '</span><br>' .
	'<span style="font-size:6px;">' . $this->firma_sac_selec . '</span><br>' .
	'<span style="font-size:6px;">N�mero de Identificacion en <a href="https://huayca.crub.uncoma.edu.ar/catedras/1.0/">huayca.crub.uncoma.edu.ar</a> ' . $this->id_programa_seleccionado . '</span>'
);
$salida->mensaje('</td>');
			// Celda de la derecha con la imagen
			$salida->mensaje('<td style="text-align: right; padding: 0; margin: 0;">');
			$salida->mensaje('<img src="/catedras/1.0/img/logo.gif" style="max-height: 20px;">'); // Ajusta el tama�o de la imagen seg�n sea necesario
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