<?php
/**
 * Cruce entre la cuenta Huayca (Keycloak) y los usuarios de Toba.
 *
 * En Keycloak el username es el DNI. En Toba el DNI esta en apex_usuario.ciu y
 * el usuario de sesion es el legajo (apex_usuario.usuario). Lo usan el login
 * por formulario (catedras_autenticacion_keycloak) y el de SAML
 * (catedras_autenticacion_saml).
 */
class catedras_usuarios
{
	/** Un DNI escrito con puntos o espacios queda en digitos; si no es un DNI, null. */
	static function normalizar_dni($texto)
	{
		$dni = preg_replace('/[\s.]/', '', (string) $texto);
		return preg_match('/^\d{6,9}$/', $dni) ? $dni : null;
	}

	/**
	 * Legajo del usuario cuyo ciu es $dni, o null si no hay ninguno.
	 * @throws toba_error_autenticacion si el DNI esta en mas de un usuario
	 */
	static function legajo_por_dni($dni)
	{
		$db = toba::instancia()->get_db();
		$filas = $db->consultar('SELECT usuario FROM apex_usuario WHERE ciu = ' . $db->quote($dni));
		if (count($filas) > 1) {
			toba::logger()->crit("LOGIN: el DNI '$dni' esta en mas de un usuario de Toba");
			throw new toba_error_autenticacion("El DNI $dni corresponde a más de un usuario. Avisale a Secretaría Académica.");
		}
		return $filas ? $filas[0]['usuario'] : null;
	}

	/** DNI (ciu) del usuario de Toba, o null si no tiene uno valido cargado. */
	static function dni_de_usuario($usuario)
	{
		$db = toba::instancia()->get_db();
		$filas = $db->consultar('SELECT ciu FROM apex_usuario WHERE usuario = ' . $db->quote($usuario));
		return $filas ? self::normalizar_dni($filas[0]['ciu']) : null;
	}

	static function existe_usuario($usuario)
	{
		$db = toba::instancia()->get_db();
		return (bool) $db->consultar('SELECT 1 FROM apex_usuario WHERE usuario = ' . $db->quote($usuario));
	}
}
