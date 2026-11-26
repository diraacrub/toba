<?php
/**
 * Entrada unica Huayca: login de catedras por redireccion a Keycloak (OIDC,
 * "authorization code" con PKCE), para compartir la sesion con Quintral y deptos.
 *
 * Quien ya entro en otro sistema Huayca vuelve de Keycloak sin escribir nada.
 * Quien no, ve la pantalla de login de Keycloak (la misma para todos).
 *
 * Lo llama ci_login::ini() en cada pedido a la pantalla de login:
 *  - ?huayca=entrar  -> redirige a Keycloak (boton "Entrar con cuenta Huayca").
 *  - ?huayca=vuelta  -> respuesta de Keycloak: canjea el codigo, cruza DNI y
 *                       legajo y abre la sesion de Toba.
 *  - ?huayca=salio   -> volvio de cerrar la sesion de Keycloak: muestra el login.
 *  - ?huayca=toba    -> formulario de legajo y clave de Toba, sin redirigir.
 *  - sin parametro (GET): en la transicion se pregunta en silencio (prompt=none)
 *    si ya hay sesion Huayca; si no la hay, se muestra el formulario. Despues
 *    de la transicion se redirige directo a Keycloak.
 *
 * El estado del pedido (state, nonce, code_verifier) y el id_token (para el
 * cierre de sesion) viven en $_SESSION['catedras_huayca'], fuera de lo de Toba.
 *
 * Si se pidio una operacion (?ai=catedras||3472, como los enlaces de Gestion
 * Academica) sin sesion abierta, se guarda ahi mismo ('destino'): Keycloak
 * vuelve a ?huayca=vuelta, sin el ai, y sin esto se caeria en el Inicio. Al
 * abrir la sesion se entra en esa operacion (ver recordar_destino y tomar_destino).
 * Sin operacion pedida, Secretaria, un departamento o un docente empieza en Gestion
 * Academica (ver empieza_en_gestion y catedras_perfiles); el resto, en el Inicio.
 *
 * El id_token se recibe directo del endpoint de tokens, por TLS y con el secreto
 * del cliente: alcanza con verificar iss, aud, nonce y vencimiento (OIDC Core
 * 3.1.3.7, punto 6). Por eso verify_ssl tiene que estar en 1 en produccion.
 *
 * Archivo en ASCII: los acentos van como \xNN (latin1, como el resto de Toba).
 */
require_once __DIR__ . '/catedras_usuarios.php';
require_once __DIR__ . '/catedras_perfiles.php';

class catedras_huayca_sso
{
	const SESION = 'catedras_huayca';
	const PARAM = 'huayca';
	const TIEMPO_LIMITE = 15;
	const TIEMPO_CHEQUEO = 3;	// para ver si Keycloak contesta antes de la consulta silenciosa
	const VIGENCIA_DESTINO = 900;	// segundos que vale la operacion pedida antes de entrar

	/** Mensaje para la pantalla (lo muestra ci_login con toba::notificacion). */
	private static $mensaje = null;
	private static $tipo_mensaje = 'error';

	static function mensaje()
	{
		return self::$mensaje;
	}

	/** 'error' o 'info', para toba::notificacion()->agregar(). */
	static function tipo_mensaje()
	{
		return self::$tipo_mensaje;
	}

	private static function conf($clave, $defecto = null)
	{
		return catedras_autenticacion_keycloak::param($clave, $defecto);
	}

	// ------------------------------------------------------------------
	// Entrada desde ci_login
	// ------------------------------------------------------------------

	/**
	 * Atiende el pedido de la pantalla de login. Puede redirigir (y terminar el
	 * pedido), abrir la sesion de Toba (lanza toba_reset_nucleo) o no hacer nada
	 * y dejar que se muestre la pantalla.
	 */
	static function procesar()
	{
		if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
			return;		// los POST son el formulario de legajo y clave
		}
		if (toba::manejador_sesiones()->existe_usuario_activo()) {
			return;
		}
		self::recordar_destino();
		$accion = $_GET[self::PARAM] ?? null;
		switch ($accion) {
			case 'entrar':
				self::redirigir_a_keycloak(false);
				return;
			case 'vuelta':
				self::procesar_vuelta();
				return;
			case 'salio':
				self::$mensaje = "Cerraste la sesi\xf3n.";
				self::$tipo_mensaje = 'info';
				return;
			case 'toba':
				return;
		}
		if (isset($_GET[apex_sesion_qs_finalizar])) {
			// Recien salio ("Salir" es ?fs=1). Toba procesa la salida antes de que el
			// contexto de ejecucion instale catedras_autenticacion_keycloak, asi que
			// su logout() no llega a correr: la sesion de Keycloak se cierra aca. Si
			// habia entrado con legajo y clave, salir() no hace nada y se muestra la
			// pantalla (sin la consulta silenciosa, para no volver a entrar solo).
			self::salir();
			return;
		}
		if (catedras_autenticacion_keycloak::en_transicion()) {
			if (self::keycloak_responde()) {
				self::redirigir_a_keycloak(true);
			} else {
				self::$mensaje = "El servidor de cuentas Huayca no responde. Pod\xe9s entrar con tu legajo y tu clave de siempre.";
			}
			return;
		}
		self::redirigir_a_keycloak(false);
	}

	/**
	 * Guarda la operacion pedida (?ai=proyecto||item) para entrar en ella al
	 * volver de Keycloak. Solo un item numerico de este proyecto que no sea el
	 * login. Queda aunque la vuelta silenciosa diga que no hay sesion Huayca:
	 * vale tambien para quien despues aprieta el boton.
	 */
	private static function recordar_destino()
	{
		$ai = $_GET[apex_hilo_qs_item] ?? null;
		if (! is_string($ai)) {
			return;
		}
		$partes = explode(apex_qs_separador, $ai);
		if (count($partes) !== 2 || $partes[0] !== toba::proyecto()->get_id()
				|| ! preg_match('/^[0-9]{1,9}$/', $partes[1])
				|| $partes[1] == toba::proyecto()->get_parametro('item_pre_sesion')) {
			return;
		}
		$_SESSION[self::SESION]['destino'] = array('item' => $partes[1], 'hora' => time());
	}

	/**
	 * La operacion guardada, si sigue vigente: array(proyecto, item) o null.
	 * Se lee una sola vez. Va en el toba_reset_nucleo del login, y el nucleo
	 * carga esa operacion en lugar de la de inicio, solo en este pedido. Si la
	 * persona no tiene permiso sobre ella, Toba lo dice igual que con la sesion
	 * abierta.
	 */
	private static function tomar_destino()
	{
		$destino = $_SESSION[self::SESION]['destino'] ?? null;
		unset($_SESSION[self::SESION]['destino']);
		if (is_array($destino) && time() - (int) $destino['hora'] <= self::VIGENCIA_DESTINO) {
			return array(toba::proyecto()->get_id(), $destino['item']);
		}
		return null;
	}

	/** true si se muestra el formulario de legajo y clave de Toba. */
	static function muestra_formulario_toba()
	{
		if (catedras_autenticacion_keycloak::en_transicion()) {
			return true;
		}
		return catedras_autenticacion_keycloak::permite_login_toba_local()
			&& (($_GET[self::PARAM] ?? null) === 'toba' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
	}

	// ------------------------------------------------------------------
	// Ida a Keycloak
	// ------------------------------------------------------------------

	private static function redirigir_a_keycloak($silencioso)
	{
		$verificador = self::aleatorio(48);
		$pedido = array(
			'state' => self::aleatorio(24),
			'nonce' => self::aleatorio(24),
			'verificador' => $verificador,
			'silencioso' => $silencioso,
			'hora' => time(),
		);
		$_SESSION[self::SESION]['pedido'] = $pedido;

		$params = array(
			'client_id' => self::conf('client_id'),
			'response_type' => 'code',
			'scope' => 'openid',
			'redirect_uri' => self::url_vuelta(),
			'state' => $pedido['state'],
			'nonce' => $pedido['nonce'],
			'code_challenge' => self::b64url(hash('sha256', $verificador, true)),
			'code_challenge_method' => 'S256',
		);
		if ($silencioso) {
			$params['prompt'] = 'none';
		}
		self::redirigir(self::url_oidc('auth') . '?' . http_build_query($params));
	}

	// ------------------------------------------------------------------
	// Vuelta de Keycloak
	// ------------------------------------------------------------------

	private static function procesar_vuelta()
	{
		$pedido = $_SESSION[self::SESION]['pedido'] ?? null;
		unset($_SESSION[self::SESION]['pedido']);
		$state = (string) ($_GET['state'] ?? '');
		if ($pedido === null || $state === '' || ! hash_equals($pedido['state'], $state)) {
			// Recargo de una vuelta vieja, o la vuelta abierta en otra pestana
			return;
		}
		if (isset($_GET['error'])) {
			$error = (string) $_GET['error'];
			if (! in_array($error, array('login_required', 'interaction_required', 'consent_required'))) {
				toba::logger()->error("LOGIN HUAYCA: Keycloak devolvio el error '$error': " . substr((string) ($_GET['error_description'] ?? ''), 0, 200));
				self::$mensaje = "No se pudo entrar con la cuenta Huayca. Prob\xe1 de nuevo.";
			}
			return;	// sin sesion Huayca: se muestra la pantalla
		}
		$codigo = (string) ($_GET['code'] ?? '');
		if ($codigo === '') {
			return;
		}
		try {
			$tokens = self::canjear_codigo($codigo, $pedido['verificador']);
			$datos = self::validar_id_token($tokens['id_token'] ?? '', $pedido['nonce']);
		} catch (toba_error_autenticacion $e) {
			self::$mensaje = $e->getMessage();
			return;
		}
		$_SESSION[self::SESION]['id_token'] = $tokens['id_token'];

		$dni = catedras_usuarios::normalizar_dni($datos['preferred_username'] ?? '');
		if ($dni === null) {
			toba::logger()->error('LOGIN HUAYCA: el usuario de Keycloak no es un DNI: ' . ($datos['preferred_username'] ?? '(vacio)'));
			self::$mensaje = "Tu cuenta Huayca no tiene un DNI como usuario. Avisale a Secretar\xeda Acad\xe9mica.";
			return;
		}
		try {
			$legajo = catedras_usuarios::legajo_por_dni($dni);
		} catch (toba_error_autenticacion $e) {
			self::$mensaje = $e->getMessage();
			return;
		}
		if ($legajo === null) {
			toba::logger()->notice("LOGIN HUAYCA: DNI $dni con cuenta Huayca pero sin usuario en catedras");
			self::$mensaje = "Tu cuenta Huayca es v\xe1lida, pero el DNI $dni no tiene acceso a C\xe1tedras. Pedile el alta a Secretar\xeda Acad\xe9mica.";
			return;
		}

		toba::logger()->info("LOGIN HUAYCA: DNI $dni entra como $legajo");
		catedras_autenticacion_keycloak::marcar_verificado($legajo);
		$destino = self::tomar_destino();
		try {
			// La clave no se usa: la persona ya se autentico en Keycloak.
			toba::manejador_sesiones()->login($legajo, self::aleatorio(16));
		} catch (toba_reset_nucleo $e) {
			// El exito: entra en la operacion que habia pedido, si pidio una.
			if ($destino !== null) {
				$e->set_item($destino);
			} elseif (self::empieza_en_gestion($tokens)) {
				// Si no pidio ninguna, Secretaria, un departamento o un docente
				// empieza en Gestion Academica (su panel, el escritorio de su
				// departamento o Mis catedras). La sesion de Toba ya quedo abierta: redirigir()
				// la guarda antes de salir.
				toba::logger()->info("LOGIN HUAYCA: $legajo empieza en Gestion Academica");
				self::redirigir(catedras_perfiles::URL_GESTION);
			}
			throw $e;
		} catch (toba_error_autenticacion $e) {
			self::$mensaje = $e->getMessage();
		} catch (toba_error $e) {
			// Usuario bloqueado, IP rechazada, etc. (toba_reset_nucleo, el exito, no es toba_error y sigue de largo)
			self::$mensaje = $e->getMessage();
		}
	}

	/**
	 * true si quien acaba de entrar empieza en Gestion Academica: por sus
	 * perfiles de catedras (catedras_perfiles) y porque Gestion Academica lo
	 * deja entrar. Para eso tiene que tener uno de los roles de Keycloak de
	 * roles_gestion (keycloak.ini; los de ROLES_AUTORIZADOS en el .env de
	 * deptos). Quien no lo tiene se queda en catedras: alla veria "no
	 * habilitado". Un director que entra a Gestion Academica por estar en
	 * DNI_AUTORIZADOS sin el rol tambien se queda aca: no es un error.
	 */
	private static function empieza_en_gestion(array $tokens)
	{
		try {
			if (! catedras_perfiles::actual()->empieza_en_gestion()) {
				return false;
			}
		} catch (Exception $e) {
			toba::logger()->error('LOGIN HUAYCA: no se pudieron leer los perfiles: ' . $e->getMessage());
			return false;
		}
		$habilitantes = array_filter(array_map('trim', explode(',', (string) self::conf('roles_gestion', 'quintral-user'))));
		return count(array_intersect(self::roles_del_token($tokens['access_token'] ?? ''), $habilitantes)) > 0;
	}

	/**
	 * Los roles del access_token, del realm y de cada cliente (como
	 * auth.roles_del_token en deptos). Sin verificar la firma: el token lo
	 * acaba de entregar Keycloak por TLS, no viene del navegador.
	 */
	private static function roles_del_token($jwt)
	{
		$partes = explode('.', (string) $jwt);
		$datos = count($partes) === 3 ? json_decode(self::b64url_decode($partes[1]), true) : null;
		if (! is_array($datos)) {
			return array();
		}
		$listas = array($datos['realm_access'] ?? null);
		if (isset($datos['resource_access']) && is_array($datos['resource_access'])) {
			$listas = array_merge($listas, array_values($datos['resource_access']));
		}
		$roles = array();
		foreach ($listas as $acceso) {
			if (is_array($acceso) && isset($acceso['roles']) && is_array($acceso['roles'])) {
				foreach ($acceso['roles'] as $r) {
					$roles[] = (string) $r;
				}
			}
		}
		return array_values(array_unique($roles));
	}

	private static function canjear_codigo($codigo, $verificador)
	{
		$datos = array(
			'grant_type' => 'authorization_code',
			'code' => $codigo,
			'redirect_uri' => self::url_vuelta(),
			'client_id' => self::conf('client_id'),
			'code_verifier' => $verificador,
		);
		if (self::conf('client_secret')) {
			$datos['client_secret'] = self::conf('client_secret');
		}
		list($estado, $json, $crudo) = self::post(self::url_oidc('token'), $datos);
		if ($estado == 200 && ! empty($json['id_token'])) {
			return $json;
		}
		toba::logger()->error("LOGIN HUAYCA: el canje del codigo respondio $estado: " . substr((string) $crudo, 0, 300));
		throw new toba_error_autenticacion("No se pudo entrar con la cuenta Huayca. Prob\xe1 de nuevo.");
	}

	/** Las afirmaciones del id_token, si es para nosotros y esta vigente. */
	private static function validar_id_token($jwt, $nonce)
	{
		$partes = explode('.', (string) $jwt);
		$datos = count($partes) === 3 ? json_decode(self::b64url_decode($partes[1]), true) : null;
		$aud = (array) ($datos['aud'] ?? array());
		$problema = null;
		if (! is_array($datos)) {
			$problema = 'id_token ilegible';
		} elseif (($datos['iss'] ?? '') !== self::url_realm()) {
			$problema = 'iss ' . ($datos['iss'] ?? '');
		} elseif (! in_array(self::conf('client_id'), $aud, true)) {
			$problema = 'aud ' . implode(',', $aud);
		} elseif (! hash_equals($nonce, (string) ($datos['nonce'] ?? ''))) {
			$problema = 'nonce';
		} elseif ((int) ($datos['exp'] ?? 0) < time() - 60) {
			$problema = 'vencido';
		}
		if ($problema !== null) {
			toba::logger()->error("LOGIN HUAYCA: id_token rechazado ($problema)");
			throw new toba_error_autenticacion("No se pudo entrar con la cuenta Huayca. Prob\xe1 de nuevo.");
		}
		return $datos;
	}

	// ------------------------------------------------------------------
	// Salida
	// ------------------------------------------------------------------

	/**
	 * Si la sesion se abrio con la cuenta Huayca, cierra tambien la de Keycloak
	 * (y con ella la entrada automatica a los otros sistemas) y vuelve al login.
	 * Si se entro con legajo y clave, no hace nada.
	 */
	static function salir()
	{
		$id_token = $_SESSION[self::SESION]['id_token'] ?? null;
		unset($_SESSION[self::SESION]);
		if (! $id_token || PHP_SAPI === 'cli') {
			return;
		}
		self::redirigir(self::url_salida($id_token));
	}

	/** URL para cerrar la sesion Huayca (tambien la usa la pantalla tras un error de acceso). */
	static function url_salida($id_token = null)
	{
		$id_token = $id_token ?: ($_SESSION[self::SESION]['id_token'] ?? null);
		$params = array(
			'client_id' => self::conf('client_id'),
			'post_logout_redirect_uri' => self::url_sistema() . '?' . self::PARAM . '=salio',
		);
		if ($id_token) {
			$params['id_token_hint'] = $id_token;
		}
		return self::url_oidc('logout') . '?' . http_build_query($params);
	}

	static function tiene_sesion_huayca()
	{
		return ! empty($_SESSION[self::SESION]['id_token']);
	}

	// ------------------------------------------------------------------
	// URLs
	// ------------------------------------------------------------------

	static function url_entrar()
	{
		return self::url_sistema() . '?' . self::PARAM . '=entrar';
	}

	static function url_formulario_toba()
	{
		return self::url_sistema() . '?' . self::PARAM . '=toba';
	}

	/** La raiz de catedras (url_sistema en keycloak.ini, o la del pedido). */
	static function url_sistema()
	{
		$url = self::conf('url_sistema');
		if ($url) {
			return rtrim($url, '/') . '/';
		}
		$https = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
		return ($https ? 'https' : 'http') . '://' . $host . $dir . '/';
	}

	private static function url_vuelta()
	{
		return self::url_sistema() . '?' . self::PARAM . '=vuelta';
	}

	private static function url_realm()
	{
		return rtrim(self::conf('url'), '/') . '/realms/' . rawurlencode(self::conf('realm', 'CRUB'));
	}

	private static function url_oidc($que)
	{
		return self::url_realm() . '/protocol/openid-connect/' . $que;
	}

	// ------------------------------------------------------------------
	// Auxiliares
	// ------------------------------------------------------------------

	/** Para no mandar a la persona a una pagina de error del navegador si Keycloak esta caido. */
	private static function keycloak_responde()
	{
		$cache = $_SESSION[self::SESION]['responde'] ?? null;
		if ($cache !== null && $cache['hora'] > time() - 60) {
			return $cache['ok'];
		}
		list($estado) = self::pedir(self::url_realm() . '/.well-known/openid-configuration', null, self::TIEMPO_CHEQUEO);
		$ok = $estado == 200;
		if (! $ok) {
			toba::logger()->error("LOGIN HUAYCA: Keycloak no responde (estado $estado)");
		}
		$_SESSION[self::SESION]['responde'] = array('ok' => $ok, 'hora' => time());
		return $ok;
	}

	private static function post($url, array $datos)
	{
		return self::pedir($url, $datos, self::TIEMPO_LIMITE);
	}

	/** @return array (estado HTTP o 0, json decodificado, respuesta cruda) */
	private static function pedir($url, $datos, $tiempo)
	{
		$verificar = self::conf('verify_ssl', '1') == '1';
		$ch = curl_init($url);
		$opciones = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => $tiempo,
			CURLOPT_CONNECTTIMEOUT => min($tiempo, 5),
			CURLOPT_SSL_VERIFYPEER => $verificar,
			CURLOPT_SSL_VERIFYHOST => $verificar ? 2 : 0,
		);
		if ($datos !== null) {
			$opciones[CURLOPT_POST] = true;
			$opciones[CURLOPT_POSTFIELDS] = http_build_query($datos);
		}
		curl_setopt_array($ch, $opciones);
		$respuesta = curl_exec($ch);
		$estado = $respuesta === false ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($respuesta === false) {
			toba::logger()->error('LOGIN HUAYCA: no se pudo contactar Keycloak: ' . curl_error($ch));
		}
		curl_close($ch);
		return array($estado, $respuesta === false ? null : json_decode($respuesta, true), $respuesta);
	}

	private static function redirigir($url)
	{
		session_write_close();
		header('Location: ' . $url, true, 302);
		header('Cache-Control: no-store');
		exit;
	}

	private static function aleatorio($bytes)
	{
		return self::b64url(random_bytes($bytes));
	}

	private static function b64url($binario)
	{
		return rtrim(strtr(base64_encode($binario), '+/', '-_'), '=');
	}

	private static function b64url_decode($texto)
	{
		return (string) base64_decode(strtr($texto, '-_', '+/') . str_repeat('=', (4 - strlen($texto) % 4) % 4));
	}
}
