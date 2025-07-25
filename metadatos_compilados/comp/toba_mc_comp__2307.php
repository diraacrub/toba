<?php

class toba_mc_comp__2307
{
	static function get_metadatos()
	{
		return array (
  '_info' => 
  array (
    'proyecto' => 'catedras',
    'objeto' => 2307,
    'anterior' => NULL,
    'identificador' => NULL,
    'reflexivo' => NULL,
    'clase_proyecto' => 'toba',
    'clase' => 'toba_ci',
    'subclase' => 'ci_login',
    'subclase_archivo' => 'login/ci_login.php',
    'objeto_categoria_proyecto' => NULL,
    'objeto_categoria' => NULL,
    'nombre' => 'Login Genérico',
    'titulo' => 'Autentificación de Usuarios',
    'colapsable' => 0,
    'descripcion' => NULL,
    'fuente_proyecto' => 'catedras',
    'fuente' => 'catedras',
    'solicitud_registrar' => NULL,
    'solicitud_obj_obs_tipo' => NULL,
    'solicitud_obj_observacion' => NULL,
    'parametro_a' => NULL,
    'parametro_b' => NULL,
    'parametro_c' => NULL,
    'parametro_d' => NULL,
    'parametro_e' => NULL,
    'parametro_f' => NULL,
    'usuario' => NULL,
    'creacion' => '2006-07-20 13:55:33',
    'punto_montaje' => 13,
    'clase_editor_proyecto' => 'toba_editor',
    'clase_editor_item' => '1000249',
    'clase_archivo' => 'nucleo/componentes/interface/toba_ci.php',
    'clase_vinculos' => NULL,
    'clase_editor' => '1000249',
    'clase_icono' => 'objetos/multi_etapa.gif',
    'clase_descripcion_corta' => 'ci',
    'clase_instanciador_proyecto' => 'toba_editor',
    'clase_instanciador_item' => '1642',
    'objeto_existe_ayuda' => NULL,
    'ap_clase' => NULL,
    'ap_archivo' => NULL,
    'ap_punto_montaje' => NULL,
    'cant_dependencias' => 6,
    'posicion_botonera' => 'abajo',
  ),
  '_info_eventos' => 
  array (
  ),
  '_info_puntos_control' => 
  array (
  ),
  '_info_ci' => 
  array (
    'ev_procesar_etiq' => NULL,
    'ev_cancelar_etiq' => NULL,
    'objetos' => NULL,
    'ancho' => NULL,
    'alto' => NULL,
    'posicion_botonera' => NULL,
    'tipo_navegacion' => NULL,
    'con_toc' => 0,
    'botonera_barra_item' => 0,
  ),
  '_info_ci_me_pantalla' => 
  array (
    0 => 
    array (
      'pantalla' => 1191,
      'identificador' => 'login',
      'etiqueta' => 'Login',
      'descripcion' => NULL,
      'tip' => NULL,
      'imagen_recurso_origen' => 'apex',
      'imagen' => NULL,
      'objetos' => NULL,
      'eventos' => NULL,
      'orden' => 1,
      'punto_montaje' => 13,
      'subclase' => 'pant_login',
      'subclase_archivo' => 'login/pant_login.php',
      'template' => NULL,
      'template_impresion' => NULL,
    ),
    1 => 
    array (
      'pantalla' => 1190,
      'identificador' => 'cambiar_contrasenia',
      'etiqueta' => 'Contraseña',
      'descripcion' => NULL,
      'tip' => NULL,
      'imagen_recurso_origen' => 'apex',
      'imagen' => NULL,
      'objetos' => NULL,
      'eventos' => NULL,
      'orden' => 2,
      'punto_montaje' => 13,
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'template' => NULL,
      'template_impresion' => NULL,
    ),
    2 => 
    array (
      'pantalla' => 1189,
      'identificador' => '2do_factor',
      'etiqueta' => 'Segundo Factor',
      'descripcion' => NULL,
      'tip' => NULL,
      'imagen_recurso_origen' => 'apex',
      'imagen' => NULL,
      'objetos' => NULL,
      'eventos' => NULL,
      'orden' => 3,
      'punto_montaje' => 13,
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'template' => NULL,
      'template_impresion' => NULL,
    ),
  ),
  '_info_obj_pantalla' => 
  array (
    0 => 
    array (
      'pantalla' => 1189,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1185,
      'orden' => 0,
      'identificador_pantalla' => '2do_factor',
      'identificador_dep' => 'form_2do_factor',
    ),
    1 => 
    array (
      'pantalla' => 1190,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1186,
      'orden' => 0,
      'identificador_pantalla' => 'cambiar_contrasenia',
      'identificador_dep' => 'form_passwd_vencido',
    ),
    2 => 
    array (
      'pantalla' => 1191,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1184,
      'orden' => 0,
      'identificador_pantalla' => 'login',
      'identificador_dep' => 'datos',
    ),
    3 => 
    array (
      'pantalla' => 1191,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1187,
      'orden' => 1,
      'identificador_pantalla' => 'login',
      'identificador_dep' => 'openid',
    ),
    4 => 
    array (
      'pantalla' => 1191,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1188,
      'orden' => 2,
      'identificador_pantalla' => 'login',
      'identificador_dep' => 'seleccion_usuario',
    ),
    5 => 
    array (
      'pantalla' => 1191,
      'proyecto' => 'catedras',
      'objeto_ci' => 2307,
      'dep_id' => 1183,
      'orden' => 3,
      'identificador_pantalla' => 'login',
      'identificador_dep' => 'cas',
    ),
  ),
  '_info_evt_pantalla' => 
  array (
  ),
  '_info_dependencias' => 
  array (
    0 => 
    array (
      'identificador' => 'seleccion_usuario',
      'proyecto' => 'catedras',
      'objeto' => 2301,
      'clase' => 'toba_ei_cuadro',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_cuadro.php',
      'subclase' => 'cuadro_autologin',
      'subclase_archivo' => 'login/cuadro_autologin.php',
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
    1 => 
    array (
      'identificador' => 'openid',
      'proyecto' => 'catedras',
      'objeto' => 2302,
      'clase' => 'toba_ei_formulario',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_formulario.php',
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
    2 => 
    array (
      'identificador' => 'cas',
      'proyecto' => 'catedras',
      'objeto' => 2304,
      'clase' => 'toba_ei_formulario',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_formulario.php',
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
    3 => 
    array (
      'identificador' => 'datos',
      'proyecto' => 'catedras',
      'objeto' => 2306,
      'clase' => 'toba_ei_formulario',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_formulario.php',
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
    4 => 
    array (
      'identificador' => 'form_passwd_vencido',
      'proyecto' => 'catedras',
      'objeto' => 2305,
      'clase' => 'toba_ei_formulario',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_formulario.php',
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
    5 => 
    array (
      'identificador' => 'form_2do_factor',
      'proyecto' => 'catedras',
      'objeto' => 2303,
      'clase' => 'toba_ei_formulario',
      'clase_archivo' => 'nucleo/componentes/interface/toba_ei_formulario.php',
      'subclase' => NULL,
      'subclase_archivo' => NULL,
      'fuente' => 'catedras',
      'parametros_a' => NULL,
      'parametros_b' => NULL,
    ),
  ),
);
	}

}

?>