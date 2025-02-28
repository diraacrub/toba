<?php
class prueba_ml extends catedras_ei_formulario_ml
{



	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	
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