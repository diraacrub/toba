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
		'ci_abm_materias' => 'abm_materias/ci_abm_materias.php',
		'ci_abm_programas' => 'abm_programas/ci_abm_programas.php',
		'ci_aval_programas' => 'aval_programas/ci_aval_programas.php',
		'catedras_autoload' => 'catedras_autoload.php',
		'dt_materias' => 'datos/dt_materias.php',
		'dt_programas' => 'datos/dt_programas.php',
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
		'huayca_comando' => 'extension_toba/huayca_comando.php',
		'ci_imprimir_programas' => 'imprimir_programas/ci_imprimir_programas.php',
		'ci_login' => 'login/ci_login.php',
		'cuadro_autologin' => 'login/cuadro_autologin.php',
		'pant_login' => 'login/pant_login.php',
		'ci_para_dptos_programas' => 'programas/ci_para_dptos_programas.php',
		'ci_programas_depto' => 'programas/ci_para_dptos_programas.php',
		'ci_para_deptos_programas' => 'programas/ci_para_dptos_programas.php',
		'ci_para_sac_programas' => 'programas/ci_para_sac_programas.php',
		'ci_programas' => 'programas/ci_programas.php',
		'ci_programas_viejo' => 'sac_/ci_para_dptos_programas.php',
	);
}
?>