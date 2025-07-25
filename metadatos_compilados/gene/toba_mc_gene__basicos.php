<?php

class toba_mc_gene__basicos
{
	static function info_basica()
	{
		return array (
  'nombre' => 'catedras',
  'descripcion' => 'CATEDRAS',
  'descripcion_corta' => 'Catedras',
  'estilo' => 'v2_azul',
  'estilo_proyecto' => 'toba',
  'con_frames' => 1,
  'frames_clase' => NULL,
  'frames_archivo' => NULL,
  'salida_impr_html_c' => NULL,
  'salida_impr_html_a' => NULL,
  'menu' => 'css',
  'menu_archivo' => 'nucleo/menu/toba_menu_css.php',
  'path_includes' => NULL,
  'path_browser' => NULL,
  'administrador' => NULL,
  'listar_multiproyecto' => 1,
  'orden' => NULL,
  'palabra_vinculo_std' => NULL,
  'version_toba' => '3.4.6',
  'requiere_validacion' => 1,
  'usuario_anonimo' => NULL,
  'usuario_anonimo_desc' => NULL,
  'usuario_anonimo_grupos_acc' => NULL,
  'validacion_intentos' => NULL,
  'validacion_intentos_min' => 5,
  'validacion_bloquear_usuario' => 1,
  'validacion_debug' => NULL,
  'sesion_tiempo_no_interac_min' => 30,
  'sesion_tiempo_maximo_min' => NULL,
  'sesion_subclase' => NULL,
  'sesion_subclase_archivo' => NULL,
  'contexto_ejecucion_subclase' => NULL,
  'contexto_ejecucion_subclase_archivo' => NULL,
  'usuario_subclase' => NULL,
  'usuario_subclase_archivo' => NULL,
  'encriptar_qs' => 0,
  'registrar_solicitud' => '1',
  'registrar_cronometro' => NULL,
  'item_inicio_sesion' => '2',
  'item_pre_sesion' => '3464',
  'item_pre_sesion_popup' => NULL,
  'item_set_sesion' => NULL,
  'log_archivo' => 1,
  'log_archivo_nivel' => 7,
  'fuente_datos' => 'catedras',
  'version' => NULL,
  'version_fecha' => NULL,
  'version_detalle' => NULL,
  'version_link' => NULL,
  'tiempo_espera_ms' => NULL,
  'navegacion_ajax' => 0,
  'codigo_ga_tracker' => NULL,
  'extension_toba' => true,
  'extension_proyecto' => false,
  'pm_impresion' => 13,
  'pm_sesion' => 13,
  'pm_contexto' => 13,
  'pm_usuario' => 13,
  'es_css3' => 1,
);
	}

	static function info_fuente__catedras()
	{
		return array (
  'proyecto' => 'catedras',
  'fuente_datos' => 'catedras',
  'descripcion' => 'Fuente catedras',
  'descripcion_corta' => 'catedras',
  'fuente_datos_motor' => 'postgres7',
  'host' => NULL,
  'punto_montaje' => NULL,
  'subclase_archivo' => NULL,
  'subclase_nombre' => NULL,
  'orden' => NULL,
  'schema' => NULL,
  'instancia_id' => 'catedras',
  'administrador' => NULL,
  'link_instancia' => 1,
  'tiene_auditoria' => 0,
  'parsea_errores' => 0,
  'permisos_por_tabla' => 0,
  'usuario' => NULL,
  'clave' => NULL,
  'base' => NULL,
  'link_base_archivo' => 1,
  'motor' => 'postgres7',
  'profile' => NULL,
  'mapeo_tablas_dt' => 
  array (
    'programas' => 2308,
    'materias' => 2343,
    'planificaciones' => 2430,
    'informes' => 2441,
  ),
);
	}

	static function info_indices_componentes()
	{
		return array (
);
	}

}

?>