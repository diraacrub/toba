<?php
/**
 * Esta clase fue y será generada automáticamente. NO EDITAR A MANO.
 * @ignore
 */
class catedras_autoload 
{
	static function existe_clase($nombre)
	{
		return isset(self::$clases[$nombre]);
	}

	static function cargar($nombre)
	{
		if (self::existe_clase($nombre)) { 
			 require_once(dirname(__FILE__) .'/'. self::$clases[$nombre]); 
		}
	}

	static protected $clases = array(
		'catedras_comando' => 'extension_toba/catedras_comando.php',
		'catedras_modelo' => 'extension_toba/catedras_modelo.php',
		'catedras_ci' => 'extension_toba/componentes/catedras_ci.php',
		'catedras_cn' => 'extension_toba/componentes/catedras_cn.php',
		'catedras_datos_relacion' => 'extension_toba/componentes/catedras_datos_relacion.php',
		'catedras_datos_tabla' => 'extension_toba/componentes/catedras_datos_tabla.php',
		'catedras_ei_arbol' => 'extension_toba/componentes/catedras_ei_arbol.php',
		'catedras_ei_archivos' => 'extension_toba/componentes/catedras_ei_archivos.php',
		'catedras_ei_calendario' => 'extension_toba/componentes/catedras_ei_calendario.php',
		'catedras_ei_codigo' => 'extension_toba/componentes/catedras_ei_codigo.php',
		'catedras_ei_cuadro' => 'extension_toba/componentes/catedras_ei_cuadro.php',
		'catedras_ei_esquema' => 'extension_toba/componentes/catedras_ei_esquema.php',
		'catedras_ei_filtro' => 'extension_toba/componentes/catedras_ei_filtro.php',
		'catedras_ei_firma' => 'extension_toba/componentes/catedras_ei_firma.php',
		'catedras_ei_formulario' => 'extension_toba/componentes/catedras_ei_formulario.php',
		'catedras_ei_formulario_ml' => 'extension_toba/componentes/catedras_ei_formulario_ml.php',
		'catedras_ei_grafico' => 'extension_toba/componentes/catedras_ei_grafico.php',
		'catedras_ei_mapa' => 'extension_toba/componentes/catedras_ei_mapa.php',
		'catedras_servicio_web' => 'extension_toba/componentes/catedras_servicio_web.php',
	);
}
?>