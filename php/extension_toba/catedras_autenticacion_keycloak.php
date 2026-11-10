<?php
/**
 * Login de catedras con la cuenta Huayca, en el formulario propio.
 *
 * Igual que deptos: la persona escribe DNI y contraseña en la pantalla de
 * catedras y esta clase los valida contra Keycloak con grant_type=password.
 * No hay redireccion a Keycloak. ci_login ya tradujo el DNI al legajo antes de
 * llamar a login(), asi que aca llega el legajo y se busca su DNI (ciu).
 *
 * Usuarios sin DNI cargado (por ejemplo el admin "toba") entran con la clave de
 * Toba si keycloak.ini tiene permite_login_toba = 1.
 *
 * Transicion: hasta el dia anterior a exclusivo_desde (keycloak.ini, AAAA-MM-DD)
 * tambien vale el usuario y la clave de Toba de siempre; la pantalla lo avisa.
 * Desde esa fecha solo la cuenta Huayca, sin tocar nada.
 *
 * Se activa con instalacion/keycloak.ini (ver deploy/keycloak.ini.dist); sin ese
 * archivo catedras usa el login comun de Toba. La instala
 * catedras_contexto_ejecucion.
 */
require_once __DIR__ . '/catedras_usuarios.php';

class catedras_autenticacion_keycloak extends toba_autenticacion_basica
{
	const ARCHIVO = 'keycloak.ini';
	const MSG_CREDENCIALES = 'El DNI o la contraseña no son correctos.';
	const TIEMPO_LIMITE = 15;

	private static $config = false;

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

	/** true mientras todavia vale tambien el usuario y la clave de Toba de siempre. */
	static function en_transicion()
	{
		$desde = self::exclusivo_desde();
		return $desde !== null && new DateTimeImmutable('today') < $desde;
	}

	function autenticar($id_usuario, $clave, $datos_iniciales = null)
	{
		$dni = catedras_usuarios::dni_de_usuario($id_usuario);
		if ($dni !== null) {
			try {
				if (self::validar_en_keycloak($dni, $clave)) {
					return true;
				}
			} catch (toba_error_autenticacion $e) {
				// Keycloak no contesto: en la transicion se sigue con la clave de Toba.
				if (! self::en_transicion()) {
					throw $e;
				}
			}
			if (self::en_transicion()) {
				return parent::autenticar($id_usuario, $clave, $datos_iniciales);
			}
			return false;
		}
		if (self::permite_login_toba_local() || self::en_transicion()) {
			return parent::autenticar($id_usuario, $clave, $datos_iniciales);
		}
		toba::logger()->notice("LOGIN: el usuario '$id_usuario' no tiene DNI cargado y no se permite el login de Toba");
		return false;
	}

	function verificar_clave_vencida($id_usuario)
	{
		// La contraseña de la cuenta Huayca la administra Keycloak.
		if (catedras_usuarios::dni_de_usuario($id_usuario) !== null) {
			return false;
		}
		return parent::verificar_clave_vencida($id_usuario);
	}

	/**
	 * true si Keycloak acepta DNI y contraseña, false si no.
	 * @throws toba_error_autenticacion si Keycloak no contesta o rechaza el pedido por otra razon
	 */
	static function validar_en_keycloak($dni, $clave)
	{
		if ($clave === null || $clave === '') {
			return false;
		}
		$url = rtrim(self::param('url'), '/') . '/realms/' . rawurlencode(self::param('realm', 'CRUB'))
			. '/protocol/openid-connect/token';
		$datos = array(
			'grant_type' => 'password',
			'client_id' => self::param('client_id'),
			'username' => $dni,
			'password' => $clave,
			'scope' => 'openid',
		);
		if (self::param('client_secret')) {
			$datos['client_secret'] = self::param('client_secret');
		}
		$verificar = self::param('verify_ssl', '1') == '1';

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($datos),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => self::TIEMPO_LIMITE,
			CURLOPT_SSL_VERIFYPEER => $verificar,
			CURLOPT_SSL_VERIFYHOST => $verificar ? 2 : 0,
		));
		$respuesta = curl_exec($ch);
		$estado = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if ($respuesta === false) {
			toba::logger()->error("LOGIN: no se pudo contactar Keycloak: $error");
			throw new toba_error_autenticacion('No se pudo contactar el servidor de cuentas. Probá de nuevo en un rato.');
		}
		$json = json_decode($respuesta, true);
		if ($estado == 200 && ! empty($json['access_token'])) {
			return true;
		}
		if (in_array($estado, array(400, 401)) && ($json['error'] ?? '') == 'invalid_grant') {
			// Mismo resultado para DNI inexistente y contraseña equivocada.
			toba::logger()->info("LOGIN: Keycloak rechazo el ingreso de $dni");
			return false;
		}
		toba::logger()->error("LOGIN: Keycloak respondio $estado: " . substr((string) $respuesta, 0, 300));
		throw new toba_error_autenticacion('El servidor de cuentas rechazó el pedido. Avisale a Sistemas.');
	}

	/**
	 * Lo que escribio la persona, convertido al usuario de Toba con el que se
	 * abre la sesion. Lo llama ci_login antes de login().
	 *
	 * - Un DNI con usuario en Toba: su legajo.
	 * - Algo que no es un DNI (ej. "toba") o que ya es un usuario: tal cual.
	 * - Un DNI sin usuario en Toba: si la contraseña es buena se le explica que
	 *   no tiene acceso; si no, el mensaje de credenciales incorrectas (no se
	 *   revela quien tiene cuenta).
	 */
	static function usuario_para_login($ingresado, $clave)
	{
		$ingresado = trim((string) $ingresado);
		$dni = catedras_usuarios::normalizar_dni($ingresado);
		if ($dni === null) {
			return $ingresado;
		}
		$legajo = catedras_usuarios::legajo_por_dni($dni);
		if ($legajo !== null) {
			return $legajo;
		}
		if (catedras_usuarios::existe_usuario($ingresado)) {
			return $ingresado;
		}
		if (self::validar_en_keycloak($dni, $clave)) {
			toba::logger()->notice("LOGIN: DNI $dni valido en Keycloak pero sin usuario en catedras");
			throw new toba_error_autenticacion("Tu cuenta Huayca es válida, pero el DNI $dni no tiene acceso a Cátedras. Pedile el alta a Secretaría Académica.");
		}
		throw new toba_error_autenticacion(self::MSG_CREDENCIALES);
	}
}
