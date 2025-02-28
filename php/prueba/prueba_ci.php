<?php
class prueba_ci extends catedras_ci
{
	function ini()
	{
	}

	function ini__operacion()
	{
	}

	function fin()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
	}

	function conf__pant_inicial(toba_ei_pantalla $pantalla)
	{
	}

	function evt__pant_inicial__entrada()
	{
	}

	function evt__pant_inicial__salida()
	{
	}

	function post_configurar()
	{
	}

	function post_eventos()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- prueba_ml --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__prueba_ml(prueba_ml $form_ml)
	{
		
	$horarios = array();
	$hora_inicio = strtotime("08:00");
	$hora_fin = strtotime("12:00");
	$intervalo = 30 * 60; // 30 minutos en segundos

	while ($hora_inicio <= $hora_fin) {
		$hora = date("H:i", $hora_inicio);
		$horarios[] = array(
	'hora' => $hora,
	//'lunes' => '', 
	//'martes' => '', 
	//'miercoles' => '',
	//'jueves' => '',
	//'viernes' => ''
			);


		$hora_inicio += $intervalo; // Sumar 30 minutos
		}

	return $horarios;
	}   
		
		
		
	

	function evt__prueba_ml__modificacion($seleccion)
	{
	}

	function conf_evt__prueba_ml__modificacion(toba_evento_usuario $evento, $fila)
	{
	}

}
?>