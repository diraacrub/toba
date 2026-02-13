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
		'ci_abm_informes' => 'abm_informes/ci_abm_informes.php',
		'ci_abm_materias' => 'abm_materias/ci_abm_materias.php',
		'ci_abm_oferta_de_optativas' => 'abm_oferta_de_optativas/ci_abm_oferta_de_optativas.php',
		'ci_abm_planificaciones' => 'abm_planificaciones/ci_abm_planificaciones.php',
		'ci_abm_programas' => 'abm_programas/ci_abm_programas.php',
		'ci_abm_registro_de_movimientos' => 'abm_registro_de_movimientos/ci_abm_registro_de_movimientos.php',
		'ci_abm_resoluciones_optativas' => 'abm_resoluciones_optativas/ci_abm_resoluciones_optativas.php',
		'ci_aval_programas' => 'aval_programas/ci_aval_programas.php',
		'catedras_autoload' => 'catedras_autoload.php',
		'ci_base_operaciones' => 'ci_base_operaciones.php',
		'ci_control' => 'control/ci_control.php',
		'ci_control_depto' => 'control/ci_control_depto.php',
		'dt_informes' => 'datos/dt_informes.php',
		'dt_materias' => 'datos/dt_materias.php',
		'dt_movimientos' => 'datos/dt_movimientos.php',
		'dt_oferta_optativas' => 'datos/dt_oferta_optativas.php',
		'dt_planificaciones' => 'datos/dt_planificaciones.php',
		'dt_programas' => 'datos/dt_programas.php',
		'dt_res_optativas' => 'datos/dt_res_optativas.php',
		'ci_datos_docentes' => 'datos_docentes/ci_datos_docentes.php',
		'catedras_comando' => 'extension_toba/catedras_comando.php',
		'catedras_modelo' => 'extension_toba/catedras_modelo.php',
		'poblar_usuarios_comando' => 'extension_toba/comandos/poblar_usuarios.php',
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
		'ci_informes' => 'informes/ci_informes.php',
		'ci_informes_aprobados' => 'informes/ci_informes_aprobados.php',
		'ci_informes_deptos' => 'informes/ci_informes_deptos.php',
		'ci_informes_sac' => 'informes/ci_informes_sac.php',
		'ci_login' => 'login/ci_login.php',
		'cuadro_autologin' => 'login/cuadro_autologin.php',
		'pant_login' => 'login/pant_login.php',
		'modelo_materia' => 'modelos/modelo_materia.php',
		'modelo_programa' => 'modelos/modelo_programa.php',
		'ci_planificaciones_aprobadas' => 'planif_aprobadas/ci_planificaciones_aprobadas.php',
		'ci_sac_planificaciones' => 'planif_aprobadas/ci_planificaciones_aprobadas.php',
		'ci_depto_planificaciones' => 'planificaciones/ci_depto_planificaciones.php',
		'ci_planificaciones' => 'planificaciones/ci_planificaciones.php',
		'ci_para_dptos_programas' => 'programas/ci_para_dptos_programas.php',
		'ci_para_sac_programas' => 'programas/ci_para_sac_programas.php',
		'ci_programas' => 'programas/ci_programas.php',
		'ci_sac_control_programas' => 'programas/ci_sac_control_programas.php',
		'ci_reporte_tabla_materias' => 'reporte_tabla_materias/ci_reporte_tabla_materias.php',
		'ci_reporte_tabla_planificaciones' => 'reporte_tabla_planificaciones/ci_reporte_tabla_planificaciones.php',
		'ci_reporte_tabla_programas' => 'reporte_tabla_programas/ci_reporte_tabla_programas.php',
		'recurso_materias' => 'rest/recurso_materias.php',
		'recurso_programas' => 'rest/recurso_programas.php',
		'ci_programas_viejo' => 'sac_/ci_para_dptos_programas.php',
		'mi_autenticacion_saml' => 'saml_onelogin/mi_autenticacion_saml.php',
		'ver_programas_aprobados' => 'ver_programas_aprobados.php',
	);
}
?>