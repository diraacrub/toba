<?php
/**
 * Login de catedras con la cuenta Huayca (Keycloak, realm CRUB).
 *
 * La cuenta Huayca se usa por redireccion a Keycloak (catedras_huayca_sso), con
 * la sesion compartida con Quintral y deptos: quien ya entro en uno no vuelve a
 * escribir la clave. Cuando vuelve de Keycloak, catedras_huayca_sso marca el
 * legajo como verificado y llama a login(); autenticar() lo deja pasar.
 *
 * El formulario de la pantalla queda para el legajo y la clave de Toba:
 *  - en la transicion (hasta el dia anterior a exclusivo_desde), para todos;
 *  - despues, solo para usuarios sin DNI cargado (ej. el admin "toba") si
 *    permite_login_toba = 1, entrando por ?huayca=toba.
 *
 * Se activa con instalacion/keycloak.ini (ver deploy/keycloak.ini.dist); sin ese
 * archivo catedras usa el login comun de Toba. La instala
 * catedras_contexto_ejecucion.
 *
 * Archivo en ASCII: los acentos van como \xNN (latin1, como el resto de Toba).
 */
require_once __DIR__ . '/catedras_usuarios.php';
require_once __DIR__ . '/catedras_huayca_sso.php';

class catedras_autenticacion_keycloak extends toba_autenticacion_basica
{
	const ARCHIVO = 'keycloak.ini';

	private static $config = false;

	/** Legajo que acaba de volver de Keycloak con la cuenta Huayca. */
	private static $verificado = null;

	/** La seccion [keycloak] de instalacion/keycloak.ini, o null si no hay. */
	static function config()
	{
		if (self::$config === false) {
			self::$config = null;
			$archivo = toba::nucleo()->toba_instalacion_dir() . '/' . self::ARCHIVO;
			if (is_file($archivo)) {
				$ini = parse_ini_file($archivo, true);
				if (! empty($ini['keycloak']['url']) && ! empty($ini['keycloak']['client_id'])) {
					self::$config = $ini['keycloak'];
				}
			}
		}
		return self::$config;
	}

	static function activa()
	{
		return self::config() !== null;
	}

	static function param($clave, $defecto = null)
	{
		$c = self::config();
		return (isset($c[$clave]) && $c[$clave] !== '') ? $c[$clave] : $defecto;
	}

	static function permite_login_toba_local()
	{
		return self::param('permite_login_toba', '0') == '1';
	}

	/** Fecha (DateTimeImmutable) desde la que solo vale la cuenta Huayca, o null si no hay transicion. */
	static function exclusivo_desde()
	{
		$f = self::param('exclusivo_desde');
		if (! $f) {
			return null;
		}
		$d = DateTimeImmutable::createFromFormat('!Y-m-d', $f);
		return $d ?: null;
	}

	/** true mientras todavia vale tambien el legajo y la clave de Toba de siempre. */
	static function en_transicion()
	{
		$desde = self::exclusivo_desde();
		return $desde !== null && new DateTimeImmutable('today') < $desde;
	}

	/** Lo llama catedras_huayca_sso justo antes de login(), con el legajo que valido Keycloak. */
	static function marcar_verificado($legajo)
	{
		self::$verificado = (string) $legajo;
	}

	function autenticar($id_usuario, $clave, $datos_iniciales = null)
	{
		if (self::entro_por_huayca($id_usuario)) {
			return true;
		}
		// Formulario: legajo y clave de Toba
		if (self::en_transicion()) {
			return parent::autenticar($id_usuario, $clave, $datos_iniciales);
		}
		if (catedras_usuarios::dni_de_usuario($id_usuario) === null && self::permite_login_toba_local()) {
			return parent::autenticar($id_usuario, $clave, $datos_iniciales);
		}
		toba::logger()->notice("LOGIN: '$id_usuario' intento entrar con la clave de Toba fuera de la transicion");
		return false;
	}

	/**
	 * Toba la llama despues de autenticar(). Quien entro con la cuenta Huayca no
	 * uso la clave de Toba: que este vencida no importa (antes lo frenaba con
	 * "La contrasena actual del usuario ha caducado"). Aca se consume la marca.
	 */
	function verificar_clave_vencida($id_usuario)
	{
		if (self::entro_por_huayca($id_usuario)) {
			self::$verificado = null;
			return false;
		}
		// La contrasena de la cuenta Huayca la administra Keycloak.
		if (catedras_usuarios::dni_de_usuario($id_usuario) !== null && ! self::en_transicion()) {
			return false;
		}
		return parent::verificar_clave_vencida($id_usuario);
	}

	private static function entro_por_huayca($id_usuario)
	{
		return self::$verificado !== null && self::$verificado === (string) $id_usuario;
	}

	/**
	 * Al salir, si se entro con la cuenta Huayca, se cierra tambien la sesion de
	 * Keycloak. Con "Salir" (?fs=1) esto no corre: Toba procesa la salida antes de
	 * instalar esta clase, y el cierre lo hace catedras_huayca_sso::procesar()
	 * en la pantalla de login que viene despues. Queda por si Toba sale por otro
	 * camino con la clase ya instalada.
	 */
	function logout()
	{
		parent::logout();
		catedras_huayca_sso::salir();
	}

	/**
	 * Lo que se escribio en el formulario, convertido al usuario de Toba. Lo
	 * llama ci_login antes de login().
	 *
	 * El formulario es para el legajo. Si alguien escribe su DNI, se lo manda
	 * al boton de la cuenta Huayca: la clave de Huayca se escribe en Keycloak,
	 * no aca, para que la sesion quede compartida con los otros sistemas.
	 */
	static function usuario_para_login($ingresado, $clave)
	{
		$ingresado = trim((string) $ingresado);
		if (catedras_usuarios::existe_usuario($ingresado)) {
			return $ingresado;
		}
		if (catedras_usuarios::normalizar_dni($ingresado) !== null) {
			throw new toba_error_autenticacion("Para entrar con tu DNI us\xe1 el bot\xf3n \"Entrar con cuenta Huayca\". Ac\xe1 va el legajo.");
		}
		return $ingresado;
	}
}
