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

class catedras_contexto_ejecucion extends toba_contexto_ejecucion
{
	function conf__inicial()
	{
		$tipo = toba::instalacion()->get_tipo_autenticacion();
		if ($tipo === 'saml_onelogin') {
			toba::manejador_sesiones()->set_autenticacion(new catedras_autenticacion_saml());
		} elseif (($tipo === 'toba' || $tipo === '' || $tipo === null) && catedras_autenticacion_keycloak::activa()) {
			toba::manejador_sesiones()->set_autenticacion(new catedras_autenticacion_keycloak());
		}
	}
}
