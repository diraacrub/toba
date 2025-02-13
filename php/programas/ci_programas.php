<?php
class ci_programas extends catedras_ci
{
	//esto es de operaciones docentes
	
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
	

function vista_pdf(toba_vista_pdf $salida)
{
	// Recuperar los datos del programa (se mantienen iguales)
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

	if (!isset($this->id_programa_seleccionado)) {
		$salida->titulo('No se ha seleccionado un programa.');
		return;
	}

	// Configurar márgenes y numeración de páginas en el PDF
	$pdf = $salida->get_pdf();
	$pdf->ezSetMargins(80, 50, 30, 30); // top, bottom, left, right
	$formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
	$pdf->ezStartPageNumbers(300, 20, 8, 'left', $formato, 1);

	// Actualización del nombre del archivo:
	// Formato: AÑO_ACADEMICO - COD_CARRERA - NOMBRE_MATERIA [cod. COD_GUARANI]
	$nombre_archivo = $this->ano_academico_selec . " - " .
						$this->cod_carrera_selec . " - " .
						$this->nombre_materia_selec . " [cod. " .
						$this->cod_guarani_selec . " ]";
	$salida->titulo($nombre_archivo);
	$salida->set_nombre_archivo($nombre_archivo . '.pdf');

	// (2) Contenido principal: campos en texto simple 
	$salida->mensaje("<b>AÑO ACADÉMICO:</b> " . $this->ano_academico_selec, 12);
	$salida->mensaje("<b>DEPARTAMENTO:</b> " . $this->depto_selec, 12);
	$salida->mensaje("<b>PROGRAMA DE CÁTEDRA:</b> " . $this->nombre_materia_selec .
						" (Cod.Guaraní: " . $this->cod_guarani_selec . ")", 12);
	$salida->mensaje("<b>OPTATIVA:</b> " . $this->optativa_selec, 12);
	$salida->mensaje("<b>CARRERA:</b> " . $this->nombre_carrera_selec . " (" . $this->cod_carrera_selec . ")", 12);
	$salida->mensaje("<b>ÁREA:</b> " . $this->area_selec, 12);
	$salida->mensaje("<b>ORIENTACIÓN:</b> " . $this->orientacion_selec, 12);
	$salida->mensaje("<b>PLAN DE ESTUDIOS ORD.:</b> " . $this->plan_ordenanzas_selec, 12);
	$salida->mensaje("<b>TRAYECTO (PEF):</b> " . $this->trayecto_selec, 12);
	$salida->mensaje("<b>CARGA HORARIA SEMANAL:</b> " . $this->horas_semanales_selec, 12);
	$salida->mensaje("<b>CARGA HORARIA TOTAL:</b> " . $this->horas_totales_selec, 12);
	$salida->mensaje("<b>RÉGIMEN:</b> " . $this->periodo_dictado_selec, 12);
	$salida->mensaje("", 10);

	// Equipo de cátedra y correlativas
	$salida->mensaje("<b>EQUIPO DE CÁTEDRA:</b>", 12);
	$salida->mensaje("   " . $this->apellido_resp_selec . ", " . $this->nombre_resp_selec . " - " . $this->cargo_resp_selec, 12);
	$salida->mensaje($this->equipo_catedra_selec, 12);
	$salida->mensaje("", 10);
	$salida->mensaje("<b>ASIGNATURAS CORRELATIVAS:</b>", 12);
	$salida->mensaje("<b>- PARA CURSAR:</b> ", 12);
	$salida->mensaje("" . $this->correlativas_para_cursar_selec, 12);
	$salida->mensaje("");
	$salida->mensaje("<b>- PARA RENDIR EXAMEN FINAL:</b>", 12);
	$salida->mensaje("" . $this->correlativas_para_aprobar_selec, 12);
	$salida->mensaje("", 10);

	// Otros apartados
	$salida->mensaje("<b>1. FUNDAMENTACIÓN:</b>", 12);
	$salida->mensaje($this->fundamentacion_selec, 12);
	$salida->mensaje("<b>2. OBJETIVOS:</b>", 12);
	$salida->mensaje($this->objetivos_selec, 12);
	$salida->mensaje("<b>3. CONTENIDOS SEGÚN PLAN DE ESTUDIOS:</b>", 12);
	$salida->mensaje($this->contenidos_minimos_selec, 12);
	$salida->mensaje("<b>4. PROGRAMA ANALÍTICO:</b>", 12);
	$salida->mensaje($this->programa_analitico_selec, 12);
	$salida->mensaje("<b>5. BIBLIOGRAFÍA:</b>", 12);
	$salida->mensaje($this->bibliografia_selec, 12);
	$salida->mensaje("<b>6. PROPUESTA METODOLÓGICA:</b>", 12);
	$salida->mensaje($this->propuesta_metodologica_selec, 12);
	$salida->mensaje("<b>7. EVALUACIÓN Y ACREDITACIÓN:</b>", 12);
	$salida->mensaje($this->evaluacion_acreditacion_selec, 12);
	$salida->mensaje("", 10);

	// (2a) Campo “8. DISTRIBUCIÓN HORARIA” con contenido en HTML
	$salida->mensaje("<b>8. DISTRIBUCIÓN HORARIA:</b>", 12);
	$this->procesar_html_para_pdf($pdf, $this->distribucion_horaria_selec);

	// (2b) Campo “9. CRONOGRAMA TENTATIVO” con contenido en HTML
	$salida->mensaje("<b>9. CRONOGRAMA TENTATIVO:</b>", 12);
	$this->procesar_html_para_pdf($pdf, $this->cronograma_tentativo_selec);

	// (3) Pie de página
	foreach ($pdf->ezPages as $pageNum => $id) {
		$pdf = $salida->get_pdf();
		$pdf->reopenObject($id);
		$pdf->addText(30, 30, 8, "Estado: " . $this->estado_selec . " - FALTAN FIRMAS");
		$pdf->addText(30, 20, 8, "Id: " . $this->id_programa_seleccionado);
		$imagen_header = toba::proyecto()->get_path().'/www/img/logo-unco-bariloche-azul-gris.jpeg';
		$pdf->addJpegFromFile($imagen_header, 50, 780, 50, 20);    //imagen, x, y, ancho, alto
		$imagen_footer = toba::proyecto()->get_path().'/www/img/logo-unco-bariloche-azul-gris.jpeg';
		$pdf->addJpegFromFile($imagen_footer, 500, 15, 50, 20);
		$pdf->closeObject();
	}
}



/**
	* Procesa un bloque de contenido HTML y lo imprime en el PDF.
	* Elimina líneas en blanco extra antes de procesar.
	*
	* @param object $pdf          Objeto PDF (de EZPDF)
	* @param string $titulo       Título de la sección
	* @param string $html_content Contenido en HTML a procesar
	*/
private function procesar_html_para_pdf($pdf, $html_content)
{
	// Limpiar saltos de línea repetidos y espacios en blanco
	$html_content = preg_replace('/(\r?\n){2,}/', "\n", $html_content);
	$html_content = trim($html_content);

	

	if (stripos($html_content, '<table') !== false) {
		$this->procesar_html($pdf, $html_content);
	} else {
		$pdf->ezText(strip_tags($html_content), 12, array('justification' => 'full'));
	}
}


/**
	* Procesa el contenido HTML para separar y mostrar tablas y texto.
	*
	* @param object $pdf  Objeto PDF (de EZPDF)
	* @param string $html Cadena HTML a procesar
	*/
private function procesar_html($pdf, $html)
{
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
	libxml_clear_errors();

	// Calcular ancho efectivo de la página (ajustado a márgenes)
	$effective_width = $pdf->ez['pageWidth'] - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'];

	$body = $dom->getElementsByTagName('body')->item(0);
	foreach ($body->childNodes as $node) {
		if ($node->nodeName == 'table') {
			// Se procesa la tabla: se centra y se ajusta al ancho efectivo
			$tabla = $this->tabla_desde_dom($node);
			$pdf->ezTable(
				$tabla['data'],
				$tabla['colNames'],
				'',
				array(
					'showLines'  => 1,
					'shaded'     => 1,
					'xPos'       => 'center',
					'width'      => $effective_width,
					'fontSize'   => 10,
				)
			);
		} else {
			// Para cualquier otro nodo (texto, párrafos, etc.)
			$texto = trim($dom->saveHTML($node));
			if (!empty($texto)) {
				$pdf->ezText(strip_tags($texto), 12, array('justification' => 'full'));
			}
		}
	}
}


/**
	* Convierte un nodo DOM de tipo <table> en un arreglo para ezTable.
	*
	* @param DOMElement $table Nodo de tabla
	* @return array Arreglo con claves 'data' (filas) y 'colNames' (cabeceras)
	*/
private function tabla_desde_dom($table)
{
	$data = array();
	$colNames = array();
	$rows = $table->getElementsByTagName('tr');
	$header_set = false;
	foreach ($rows as $row) {
		$rowData = array();
		foreach ($row->childNodes as $cell) {
			if (in_array($cell->nodeName, array('th', 'td'))) {
				$cellText = trim($cell->textContent);
				$rowData[] = $cellText;
			}
		}
		if (!$header_set && !empty($rowData)) {
			$colNames = $rowData;
			$header_set = true;
		} elseif (!empty($rowData)) {
			$data[] = $rowData;
		}
	}
	return array('data' => $data, 'colNames' => $colNames);
}


	
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

			// Definir carreras que no muestran horas teóricas prácticas
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			// Verificar si la carrera pertenece a las que están en el array
			if (in_array($this->cod_carrera_selec, $carreras_coneau)) {
			//if (in_array($datos_programa['cod_carrera'], $carreras_coneau)) {
				// Hacer el campo "horas_teoricopracticas" solo lectura
				$form->set_solo_lectura(array('horas_teoricopracticas'), true);
					//toba::notificacion()->agregar("No editable");
			} else {
				// Asegurarse de que el campo sea editable
				$form->set_solo_lectura(array('horas_teoricopracticas'), false);
				//toba::notificacion()->agregar("Editable");        
			}
		}
	} else {
		// Si no hay datos cargados, eliminar el evento de eliminar
		$this->pantalla()->eliminar_evento('eliminar');
	}
	
toba::notificacion()->agregar("En esta sección usted puede completar los datos del Programa, guardar los cambios para seguir completando en otra ocasión y, cuando esté listo firmar y enviar al Departamento",'info');



	
}
	
function evt__formulario_con_todo__modificacion($datos) {

	// Manejo de comentarios
	$estado= isset($datos['estado']) ? $datos['estado'] : "";

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
			// Si el estado cambia a 'depto', actualizar 'docente' con la firma electrónica

			// Obtener el nombre completo del usuario SAC
			$nombre_completo = toba::usuario()->get_nombre();

			// Crear un objeto DateTime con la zona horaria de Argentina
			$timestamp_obj = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
			$formatted_timestamp = $timestamp_obj->format('Y-m-d H:i:s');

			// Crear el contenido para 'firma_docente'
			$datos['firma_doc'] = "Firmado electrónicamente por $nombre_completo - Responsable de Cátedra - $formatted_timestamp";
		}
	}
	// *** Fin de la lógica para actualizar firma electrónica ***
	$fundamentacion = isset($datos['fundamentacion']) ? $datos['fundamentacion'] : "";
	$objetivos = isset($datos['objetivos']) ? $datos['objetivos'] : "";
	$programa_analitico = isset($datos['programa_analitico']) ? $datos['programa_analitico'] : "";
	$bibliografia = isset($datos['bibliografia']) ? $datos['bibliografia'] : "";
	$propuesta_metodologica = isset($datos['propuesta_metodologica']) ? $datos['propuesta_metodologica'] : "";
	$evaluacion_acreditacion = isset($datos['evaluacion_acreditacion']) ? $datos['evaluacion_acreditacion'] : "";
	$distribucion_horaria = isset($datos['distribucion_horaria']) ? $datos['distribucion_horaria'] : "";
	$cronograma_tentativo = isset($datos['cronograma_tentativo']) ? $datos['cronograma_tentativo'] : "";


	// *** Control de horas: Validar que el subtotal de horas coincida con la carga horaria total ***
	// Se obtienen los valores de horas enviados en el formulario (o se asigna 0 por defecto)
	$horas_totales         = isset($datos['horas_totales']) ? $datos['horas_totales'] : 0;
	$horas_practicas       = isset($datos['horas_practicas']) ? $datos['horas_practicas'] : 0;
	$horas_teoricas        = isset($datos['horas_teoricas']) ? $datos['horas_teoricas'] : 0;
	$horas_teoricopracticas= isset($datos['horas_teoricopracticas']) ? $datos['horas_teoricopracticas'] : 0;
	$cod_carrera           = isset($datos['cod_carrera']) ? $datos['cod_carrera'] : 0;

	// Definir las carreras que no contemplan horas teórico-prácticas
	$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

	if (in_array($cod_carrera, $carreras_coneau)) {
			// Para estas carreras, el subtotal se calcula sin las horas teórico-prácticas
			$subtotal_horas = $horas_practicas + $horas_teoricas;
			// Se fuerza a cero el valor de horas teórico-prácticas
			$datos['horas_teoricopracticas'] = 0;
	} else {
			// Para las demás, se suma todos los tipos de horas
			$subtotal_horas = $horas_practicas + $horas_teoricas + $horas_teoricopracticas;
	}

	// Validar que el subtotal de horas coincida con la carga horaria total
	if ($subtotal_horas != $horas_totales) {
			// Si no coinciden, se reinician los valores de horas
			$datos['horas_teoricas'] = 0;
			$datos['horas_practicas'] = 0;
			$datos['horas_teoricopracticas'] = 0;
			$datos['horas_totales'] = 0;

			// Se cambia el estado a "docente"
			$datos['estado'] = 'docente';

			// Guardar los datos modificados antes de lanzar el error
			$this->dep('datos')->tabla('programas')->set($datos);
			$this->dep('datos')->tabla('programas')->sincronizar();

			// Lanzar el error para que el usuario corrija la discrepancia en las horas
			throw new toba_error("La suma de horas no coincide con la carga horaria total por plan de estudios, por favor corrija.");
	}
	// *** Fin del control de horas ***
	
		// Validar estado es depto y faltan cargar datos obligatorios
if ($estado === "depto" && (
	empty($fundamentacion) ||
	empty($objetivos) ||
	empty($programa_analitico) ||
	empty($bibliografia) ||
	empty($propuesta_metodologica) ||
	empty($evaluacion_acreditacion) ||
	empty($distribucion_horaria) ||
	empty($cronograma_tentativo) ||
	($horas_practicas == 0 && $horas_teoricas == 0)
)) {
	// Cambiar el estado a "docente"
	$datos['estado'] = 'docente';

	// Guardar los datos modificados antes del error
	$this->dep('datos')->tabla('programas')->set($datos);
	$this->dep('datos')->sincronizar();
	throw new toba_error("Falta cargar datos obligatorios, por favor corrija.");
}


	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar al usuario que se guardó correctamente
	toba::notificacion()->agregar("Su programa ha sido guardado correctamente", 'info');

	// Sincronizar los datos con la base de datos
	$this->dep('datos')->tabla('programas')->sincronizar();
}


	
function evt__formulario_con_todo__modificacion_2025($datos) {

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

		if($estado_nuevo === 'depto') {
			// Si el estado cambia a 'depto', actualizar 'docente' con la firma electrónica

			// Obtener el nombre completo del usuario SAC
			$nombre_completo = toba::usuario()->get_nombre();

			// Crear un objeto DateTime con la zona horaria de Argentina
			$timestamp_obj = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
			$formatted_timestamp = $timestamp_obj->format('Y-m-d H:i:s');

			// Crear el contenido para 'firma_docente'
			$datos['firma_doc'] = "Firmado electrónicamente por $nombre_completo - Responsable de Cátedra - $formatted_timestamp";
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

function evt__formulario_con_todo__modificacion_vieja($datos) {
	// Obtener los datos obligatorios
	$this->id_programa_seleccionado = toba::memoria()->get_dato_operacion('id_programa_seleccionado');
	$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

	$estado = isset($datos['estado']) ? $datos['estado'] : "docente";
	$equipo_catedra = isset($datos['equipo_catedra']) ? $datos['equipo_catedra'] : "";
	$fundamentacion = isset($datos['fundamentacion']) ? $datos['fundamentacion'] : "";
	$objetivos = isset($datos['objetivos']) ? $datos_programa['objetivos'] : "";
	$programa_analitico = isset($datos['programa_analitico']) ? $datos_programa['programa_analitico'] : "";
	$bibliografia = isset($datos['bibliografia']) ? $datos_programa['bibliografia'] : "";
	$propuesta_metodologica = isset($datos['propuesta_metodologica']) ? $datos_programa['propuesta_metodologica'] : "";
	$evaluacion_acreditacion = isset($datos['evaluacion_acreditacion']) ? $datos_programa['evaluacion_acreditacion'] : "";
	$distribucion_horaria = isset($datos['distribucion_horaria']) ? $datos_programa['distribucion_horaria'] : "";
	$horas_teoricas = isset($datos['horas_teoricas']) ? $datos_programa['horas_teoricas'] : "";
	$horas_practicas = isset($datos['horas_practicas']) ? $datos_programa['horas_practicas'] : "";
	$horas_teoricopracticas = isset($datos['horas_teoricopracticas']) ? $datos_programa['horas_teoricopracticas'] : "";
	$cronograma_tentativo = isset($datos['cronograma_tentativo']) ? $datos_programa['cronograma_tentativo'] : "";
	//
	// Obtener las horas
	$horas_totales = isset($datos['horas_totales']) ? $datos['horas_totales'] : 0;
	$horas_practicas = isset($datos['horas_practicas']) ? $datos['horas_practicas'] : 0;
	$horas_teoricas = isset($datos['horas_teoricas']) ? $datos['horas_teoricas'] : 0;
	$horas_teoricopracticas = isset($datos['horas_teoricopracticas']) ? $datos['horas_teoricopracticas'] : 0;
	$cod_carrera = isset($datos['cod_carrera']) ? $datos['cod_carrera'] : 0;
	// Definir carreras que no muestran horas teóricas prácticas
	$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');
	// Calcular el subtotal de horas dependiendo si es coneau o no 
	
		if (in_array($cod_carrera, $carreras_coneau)) {
		$subtotal_horas = $horas_practicas + $horas_teoricas;
			toba::notificacion()->agregar("carrera: $cod_carrera, acredita ante coneau",'info');
			
			//pone en cero las horas teoricopracticas  
			$datos['horas_teoricopracticas'] = 0;

		} else {
		$subtotal_horas = $horas_practicas + $horas_teoricas + $horas_teoricopracticas;
			toba::notificacion()->agregar("carrera: $cod_carrera, no acredita ante coneau",'info');
		}  
		
		
// Validar si las horas coinciden
if ($subtotal_horas != $horas_totales) {
	// Asignar 0 a las horas
	$datos['horas_teoricas'] = 0;
	$datos['horas_practicas'] = 0;
	$datos['horas_teoricopracticas'] = 0;
	$datos['horas_totales'] = 0;

	// Cambiar el estado a "docente"
	$datos['estado'] = 'docente';

	// Guardar los datos modificados antes del error
	$this->dep('datos')->tabla('programas')->set($datos);
	$this->dep('datos')->sincronizar();

	// Lanzar el error para que el usuario corrija las horas
	throw new toba_error("La suma de horas no coincide con la carga horaria total por plan de estudios, por favor corrija.");
}

	
	// Validar estado es depto y faltan cargar datos obligatorios
		if ($estado === "depto" && empty($fundamentacion)) {
				// Cambiar el estado a "docente"
			$datos['estado'] = 'docente';

			// Guardar los datos modificados antes del error
			$this->dep('datos')->tabla('programas')->set($datos);
			$this->dep('datos')->sincronizar();
			throw new toba_error("Falta cargar datos obligatorios, por favor corrija.");
		}

	

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
	toba::notificacion()->agregar("Su programa ha sido guardado correctamente",$this->cod_carrera_selec,'info');

	// Refrescar el formulario para mostrar el nuevo subtotal
	//$this->dep('datos')->tabla('programas')->sincronizar();
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

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_depto_sac_aprobado();
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
		";
	}



	

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------



}
?>