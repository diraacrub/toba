<?php
class prueba_ml extends catedras_ei_formulario_ml
{
function conf__datos()
{

	$horarios = array();
	$hora_inicio = strtotime("08:00");
	$hora_fin = strtotime("21:00");
	$intervalo = 30 * 60; // 30 minutos en segundos

	while ($hora_inicio <= $hora_fin) {
		$hora = date("H:i", $hora_inicio);
		$horarios[] = array(
	'hora' => $hora,
	'lunes' => '', 
	'martes' => '', 
	'miercoles' => '',
	'jueves' => '',
	'viernes' => ''
			);


		$hora_inicio += $intervalo; // Sumar 30 minutos
	}

	return $horarios;
}


	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
{
	echo "
	//---- Agregar validación si es necesario ----
	
	{$this->objeto_js}.evt__validar_datos = function()
	{
		// Validar que cada horario tenga al menos un día seleccionado
		for (let i = 0; i < this.filas().length; i++) {
			var hora = this.ef('hora').ir_a_fila(i).get_estado();
			var lunes = this.ef('lunes').ir_a_fila(i).get_estado();
			var martes = this.ef('martes').ir_a_fila(i).get_estado();
			var miercoles = this.ef('miercoles').ir_a_fila(i).get_estado();
			
			if (hora !== '' && lunes === '' && martes === '' && miercoles === '') {
				this.ef('hora').ir_a_fila(i).mostrar_mensaje_error('Debe seleccionar al menos un día para esta hora.');
				return false;
			}
		}
		return true;
	}

	//---- Agregar nuevas filas dinámicamente ----
	{$this->objeto_js}.evt__agregar_fila = function()
	{
		var fila = this.nueva_fila();
		this.ef('hora').ir_a_fila(fila).set_estado('');
		this.ef('lunes').ir_a_fila(fila).set_estado('');
		this.ef('martes').ir_a_fila(fila).set_estado('');
		this.ef('miercoles').ir_a_fila(fila).set_estado('');
	}
	";
}

	
	
	
	function extender_objeto_js_original()
	{
		echo "
		//---- Validacion general ----------------------------------
		
		{$this->objeto_js}.evt__validar_datos = function()
		{
		}
		
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__lunes__procesar = function(es_inicial, fila)
		{
		}
		
		{$this->objeto_js}.evt__martes__procesar = function(es_inicial, fila)
		{
		}
		
		{$this->objeto_js}.evt__miercoles__procesar = function(es_inicial, fila)
		{
		}
		
		//---- Validacion de EFs -----------------------------------
		
		{$this->objeto_js}.evt__lunes__validar = function(fila)
		{
		}
		
		{$this->objeto_js}.evt__martes__validar = function(fila)
		{
		}
		
		{$this->objeto_js}.evt__miercoles__validar = function(fila)
		{
		}
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__hora__procesar = function(es_inicial, fila)
		{
		}
		
		//---- Validacion de EFs -----------------------------------
		
		{$this->objeto_js}.evt__hora__validar = function(fila)
		{
		}
		";
	}


}
?>