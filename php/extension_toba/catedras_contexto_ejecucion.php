<?php
/**
 * Contexto de ejecucion del proyecto catedras (apex_proyecto.contexto_ejecucion_subclase).
 *
 * Elige como se entra, segun la configuracion de cada instalacion:
 *  - instalacion/keycloak.ini presente (y login comun de Toba en instalacion.ini):
 *    formulario propio con la cuenta Huayca (catedras_autenticacion_keycloak).
 *  - instalacion.ini con autenticacion = "saml_onelogin": redireccion a Keycloak
 *    por SAML (catedras_autenticacion_saml).
 *  - ninguna de las dos: el login comun de Toba, sin cambios.
 * En los dos primeros casos se entra con el DNI y la sesion se abre con el legajo.
 */
require_once __DIR__ . '/catedras_autenticacion_saml.php';
require_once __DIR__ . '/catedras_autenticacion_keycloak.php';
require_once __DIR__ . '/catedras_salida.php';

class catedras_contexto_ejecucion extends toba_contexto_ejecucion
{
	// Opciones del menu que abren un sitio externo en una pestana nueva, en vez
	// de reemplazar la de Toba (asi no se pierde la navegacion). El window.open
	// sale del clic en el menu, por eso el navegador no lo bloquea.
	// item => url
	private static $menu_externo = array(
		'3516' => 'https://huayca.crub.uncoma.edu.ar/programas/',	// APROBADOS CRUB > Ver Programas Aprobados
	);

	// Opciones del menu que por ahora solo muestran un aviso (la ventana de
	// notificacion de Toba), sin ir a ningun lado. item => array(titulo, texto).
	// Va dentro de onclick='...' y de un string JS con comillas dobles: sin
	// comillas de ningun tipo, y los acentos como \u00e1.
	//
	// DOCENTES > Salidas: en 2027 pasa a $menu_externo con
	// 'https://huayca.crub.uncoma.edu.ar/deptos/salidas-de-campo/' (las salidas
	// de campo de deptos, con la misma sesion Huayca).
	private static $menu_aviso = array(
		'3535' => array(
			'Salidas de campo',
			'A partir de 2027, las salidas planificadas y los formularios de salidas deber\u00e1n gestionarse por este medio.',
		),
	);

	function conf__inicial()
	{
		$this->configurar_autenticacion();
		$this->configurar_menu();
		if (PHP_SAPI !== 'cli') {
			catedras_salida::registrar();	// pie institucional en las paginas
		}
	}

	protected function configurar_menu()
	{
		if (PHP_SAPI === 'cli' || toba_nucleo::instancia()->es_acceso_rest()
				|| !toba::manejador_sesiones()->existe_usuario_activo()) {
			return;
		}
		$menu = toba::menu();
		foreach (self::$menu_externo as $item => $url) {
			// Va dentro de onclick='...': sin comillas simples
			$menu->set_datos_opcion($item, array(
				'js' => 'window.open("' . $url . '", "_blank", "noopener"); return false;',
			));
		}
		foreach (self::$menu_aviso as $item => $aviso) {
			list($titulo, $texto) = $aviso;
			$menu->set_datos_opcion($item, array(
				'js' => 'notificacion.limpiar(); notificacion.agregar("' . $texto . '", "info");'
					. ' notificacion.set_titulo_ventana("' . $titulo . '"); notificacion.mostrar(); return false;',
			));
		}
	}

	protected function configurar_autenticacion()
	{
		$tipo = toba::instalacion()->get_tipo_autenticacion();
		if ($tipo === 'saml_onelogin') {
			toba::manejador_sesiones()->set_autenticacion(new catedras_autenticacion_saml());
		} elseif (($tipo === 'toba' || $tipo === '' || $tipo === null) && catedras_autenticacion_keycloak::activa()) {
			toba::manejador_sesiones()->set_autenticacion(new catedras_autenticacion_keycloak());
		}
	}
}
