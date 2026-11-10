<?php
/**
 * Login con Keycloak (SAML) que cruza el DNI con el legajo.
 *
 * Keycloak manda el DNI (su username) en el atributo SAML configurado en
 * saml_onelogin.ini (atributo_usuario = uid). En Toba el DNI esta en
 * apex_usuario.ciu y el usuario de sesion es el legajo (apex_usuario.usuario).
 *
 * Es la funcion recuperar_usuario_toba() de la documentacion "Integracion de
 * SIU Toba con Keycloak via SAML", que antes se pegaba en el nucleo
 * (vendor/.../toba_autenticacion_saml_onelogin.php) y se perdia con cada
 * composer install. Ahora vive en el proyecto: la instala
 * catedras_contexto_ejecucion cuando instalacion.ini tiene
 * autenticacion = "saml_onelogin".
 *
 * Hoy el login de catedras es el formulario propio (catedras_autenticacion_keycloak);
 * esta queda como alternativa.
 */
require_once __DIR__ . '/catedras_usuarios.php';

class catedras_autenticacion_saml extends toba_autenticacion_saml_onelogin
{
	/**
	 * @return string legajo (apex_usuario.usuario) del usuario cuyo ciu es el DNI recibido
	 */
	protected function recuperar_usuario_toba()
	{
		$atributos = $this->get_atributos_usuario();
		$dni = trim(utf8_d_seguro($atributos[$this->atributo_usuario][0] ?? ''));
		if ($dni === '') {
			toba::logger()->crit("SAML LOGIN: no llego el atributo '{$this->atributo_usuario}' desde Keycloak");
			throw new toba_error_autenticacion("No se recibio el DNI desde Keycloak.");
		}
		return self::legajo_por_dni($dni);
	}

	/** Busca el legajo por DNI. Publica y estatica para poder probarla sin SAML. */
	static function legajo_por_dni($dni)
	{
		$legajo = catedras_usuarios::legajo_por_dni($dni);
		if ($legajo === null) {
			toba::logger()->crit("SAML LOGIN: el DNI '$dni' no esta en apex_usuario.ciu");
			throw new toba_error_autenticacion("El DNI '$dni' no esta dado de alta en el sistema.");
		}
		return $legajo;
	}
}
