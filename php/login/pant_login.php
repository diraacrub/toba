<?php
/**
 * Pantalla de login de catedras.
 *
 * Con la cuenta Huayca activa (instalacion/keycloak.ini) se ve como las
 * pantallas de cuenta de Huayca (Quintral, deptos): campo azul, el sello,
 * "/cátedras", la tarjeta blanca con el boton "Entrar con cuenta Huayca" (va a
 * Keycloak, ver catedras_huayca_sso), "Activa tu cuenta" y, al pie, la franja
 * institucional (la misma de las demas paginas: catedras_salida::pie_html()).
 *
 * La pantalla empuja a pasar a la cuenta Huayca antes de que la clave vieja
 * deje de valer (exclusivo_desde):
 *  - el legajo y la clave de Toba van plegados detras de un enlace chico;
 *  - quien entra con ellos ve, antes de seguir, una tarjeta que le pide
 *    activar la cuenta Huayca (con un boton que le manda el mail) o, si ya la
 *    uso alguna vez, que entre con ella (catedras_ingresos).
 * El formulario sigue siendo el de Toba (sus efs, su JS, el token CSRF): solo
 * se lo envuelve y se lo reestila.
 *
 * Sin keycloak.ini queda el layout original de Toba.
 *
 * Archivo en ASCII: los acentos van como entidades HTML.
 */
class pant_login extends toba_ei_pantalla
{
	function generar_layout()
	{
		if (class_exists('catedras_autenticacion_keycloak', false) && catedras_autenticacion_keycloak::activa()) {
			$this->generar_layout_huayca();
		} else {
			$this->generar_layout_toba();
		}
	}

	protected function generar_layout_huayca()
	{
		$esc = toba::escaper();
		$sello = toba_recurso::imagen_proyecto('huayca-sello.png');
		$aviso = toba::manejador_sesiones()->existe_usuario_activo() && catedras_ingresos::hay_aviso();

		echo $this->estilos_huayca();
		// La pagina de login de Toba no declara viewport: en el celular se veia
		// a 980px, chiquita. Se agrega al <head> desde aca.
		echo "<script>(function(){if(!document.querySelector('meta[name=viewport]')){var m=document.createElement('meta');"
			. "m.name='viewport';m.content='width=device-width, initial-scale=1';document.head.appendChild(m);}})();</script>";
		echo "<div class='hc-pagina'>";
		echo "<div class='hc-centro'>";
		echo "<header class='hc-marca'>";
		echo "<img class='hc-sello' src='" . $esc->escapeHtmlAttr($sello) . "' alt='' aria-hidden='true'>";
		echo "<div><div class='hc-nombre'>/c&aacute;tedras</div><div class='hc-bajada'>Programas, planificaciones e informes</div></div>";
		echo "</header>";

		if ($aviso) {
			$this->tarjeta_pasar_a_huayca();
		} else {
			$this->tarjeta_login();
			echo "<nav class='hc-enlaces'>";
			echo "<a href='" . $esc->escapeHtmlAttr(self::url_cuenta('url_reset', 'reset-password')) . "'>&iquest;Olvidaste tu contrase&ntilde;a?</a>";
			echo "</nav>";
		}

		echo "</div>";	// .hc-centro
		echo catedras_salida::pie_html();
		echo "</div>";	// .hc-pagina
	}

	/** Pantallas de cuenta de Quintral (activar, restablecer), con ?app=catedras. */
	protected static function url_cuenta($param, $pantalla)
	{
		return catedras_autenticacion_keycloak::param($param, "https://huayca.crub.uncoma.edu.ar/quintral/$pantalla?app=catedras");
	}

	/**
	 * El login: el boton de la cuenta Huayca primero, activar la cuenta segundo y,
	 * en la transicion, el legajo y la clave vieja plegados detras de un enlace
	 * (cuesta un clic mas). Se abre solo si se acaba de intentar entrar con ellos.
	 */
	protected function tarjeta_login()
	{
		$esc = toba::escaper();
		echo "<main class='hc-tarjeta'>";
		echo "<div class='hc-cabeza'>";
		echo "<h1 class='hc-titulo'>Iniciar sesi&oacute;n</h1>";
		echo "<p class='hc-destino'>para entrar a <strong>C&aacute;tedras</strong></p>";
		echo "</div>";

		echo "<a class='hc-boton' href='" . $esc->escapeHtmlAttr(catedras_huayca_sso::url_entrar()) . "'>Entrar con cuenta Huayca</a>";
		if (catedras_huayca_sso::tiene_sesion_huayca()) {
			// Volvio de Keycloak pero no pudo entrar (DNI sin acceso a catedras, etc.)
			echo "<p class='hc-intro'><a href='" . $esc->escapeHtmlAttr(catedras_huayca_sso::url_salida()) . "'>Entrar con otra cuenta Huayca</a></p>";
		}
		echo "<p class='hc-activar'>&iquest;Primera vez? <a href='" . $esc->escapeHtmlAttr(self::url_cuenta('url_activar', 'activate')) . "'>"
			. "Activ&aacute; tu cuenta</a></p>";

		if ($this->existe_dependencia('datos')) {
			if (catedras_autenticacion_keycloak::en_transicion()) {
				// exclusivo_desde es el primer dia SOLO Huayca
				$desde = catedras_autenticacion_keycloak::exclusivo_desde();
				$hasta = $desde->modify('-1 day');
				$dias = catedras_ingresos::dias_restantes();
				$abierto = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') ? ' open' : '';
				echo "<details class='hc-viejo'$abierto>";
				echo "<summary><span class='hc-viejo-tit'>Entrar con legajo y clave vieja</span>"
					. "<span class='hc-viejo-plazo'>hasta el " . self::fecha_larga($hasta) . " &middot; " . self::faltan($dias) . "</span></summary>";
				echo "<p class='hc-viejo-nota'>Tu n&uacute;mero de legajo (sin puntos) y tu clave de C&aacute;tedras. "
					. "Desde el " . self::fecha_larga($desde) . ", solo con la cuenta Huayca.</p>";
				$this->dep('datos')->generar_html();
				echo "</details>";
			} else {
				echo "<div class='hc-separador'><span>usuario y clave de Toba</span></div>";
				$this->dep('datos')->generar_html();
			}
		}
		echo "</main>";
	}

	/**
	 * Despues de entrar con la clave vieja, antes de seguir a Catedras: que pase
	 * a la cuenta Huayca. Si ya entro alguna vez con ella, solo se le recuerda
	 * usar el boton; si no, el boton principal le manda el mail de activacion
	 * (el mismo de Quintral, que ya sabe a donde mandarlo por el DNI).
	 * El ultimo mes el plazo va en naranja.
	 */
	protected function tarjeta_pasar_a_huayca()
	{
		$esc = toba::escaper();
		$usuario = toba::usuario()->get_id();
		$dni = catedras_usuarios::dni_de_usuario($usuario);
		$ya_la_tiene = catedras_ingresos::uso_huayca($usuario);
		$dias = catedras_ingresos::dias_restantes();
		$desde = catedras_autenticacion_keycloak::exclusivo_desde();
		$urgente = ($dias !== null && $dias <= 30) ? ' hc-urgente' : '';
		$seguir = $this->controlador()->url_inicio();
		// Sale de la sesion de la clave vieja y va derecho a la cuenta Huayca
		$huayca = catedras_huayca_sso::url_sistema() . '?' . apex_sesion_qs_finalizar . '=1&' . catedras_huayca_sso::PARAM . '=entrar';

		echo "<main class='hc-tarjeta hc-pasaje$urgente'>";
		if ($ya_la_tiene) {
			echo "<p class='hc-plazo'>" . ucfirst(self::faltan($dias)) . "</p>";
			echo "<h1 class='hc-titulo'>Ya ten&eacute;s tu cuenta Huayca</h1>";
			echo "<p class='hc-intro'>La pr&oacute;xima vez entr&aacute; con el bot&oacute;n azul: es la misma cuenta de Quintral y deptos. "
				. "Desde el " . self::fecha_larga($desde) . " la clave vieja deja de funcionar.</p>";
			echo "<a class='hc-boton' href='" . $esc->escapeHtmlAttr($huayca) . "'>Entrar con cuenta Huayca ahora</a>";
			echo "<div class='hc-otras'><a href='" . $esc->escapeHtmlAttr($seguir) . "'>Seguir a C&aacute;tedras</a></div>";
		} else {
			echo "<h1 class='hc-titulo'>" . ($dias > 1 ? "Te quedan $dias d&iacute;as" : ($dias == 1 ? 'Te queda 1 d&iacute;a' : 'Hoy es el &uacute;ltimo d&iacute;a'))
				. " para pasar a tu cuenta Huayca</h1>";
			echo "<p class='hc-intro'>Desde el " . self::fecha_larga($desde) . " solo vas a poder entrar con tu DNI y la cuenta Huayca, "
				. "la misma de Quintral y deptos. Activarla te lleva 2 minutos.</p>";
			$api = catedras_autenticacion_keycloak::param('url_api_activar', '/quintral/api/auth/password-reset');
			echo "<button type='button' class='hc-boton' id='hc-mandar' data-api='" . $esc->escapeHtmlAttr($api) . "'"
				. " data-dni='" . $esc->escapeHtmlAttr((string) $dni) . "'>Mandame el mail para activarla</button>";
			echo "<p class='hc-resultado' id='hc-resultado' role='status' aria-live='polite'></p>";
			echo "<div class='hc-otras'>";
			echo "<a href='" . $esc->escapeHtmlAttr($huayca) . "'>Ya la tengo: entrar con Huayca</a>";
			echo "<a href='" . $esc->escapeHtmlAttr($seguir) . "'>Seguir con la clave vieja por esta vez</a>";
			echo "</div>";
			echo $this->js_mandar_mail();
		}
		echo "</main>";
	}

	/** "faltan 80 dias", "falta 1 dia", "ultimo dia" */
	protected static function faltan($dias)
	{
		if ($dias === null) {
			return '';
		}
		if ($dias <= 0) {
			return '&uacute;ltimo d&iacute;a';
		}
		return $dias == 1 ? 'falta 1 d&iacute;a' : "faltan $dias d&iacute;as";
	}

	/**
	 * El boton "Mandame el mail": el endpoint de activacion de Quintral (mismo
	 * dominio), con el DNI que ya conoce catedras. Textos con \u: la pagina de
	 * Toba es latin1 y este archivo ASCII.
	 */
	protected function js_mandar_mail()
	{
		return <<<'JS'
<script>
(function () {
	var boton = document.getElementById('hc-mandar');
	var salida = document.getElementById('hc-resultado');
	if (!boton) { return; }
	boton.addEventListener('click', function () {
		boton.disabled = true;
		boton.textContent = 'Mandando\u2026';
		salida.className = 'hc-resultado';
		fetch(boton.getAttribute('data-api'), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ username: boton.getAttribute('data-dni'), flow: 'activate', app: 'catedras' })
		}).then(function (r) { return r.json(); }).then(function (r) {
			if (r && r.success) {
				boton.textContent = 'Mail enviado';
				salida.className = 'hc-resultado hc-ok';
				salida.textContent = 'Listo: te mandamos el mail' + (r.email_hint ? ' a ' + r.email_hint : '') +
					'. Abrilo, eleg\u00ed tu contrase\u00f1a y la pr\u00f3xima vez entr\u00e1 con el bot\u00f3n azul.';
			} else {
				boton.disabled = false;
				boton.textContent = 'Mandame el mail para activarla';
				salida.className = 'hc-resultado hc-error';
				salida.textContent = 'No encontramos tu cuenta Huayca con tu DNI. Escribile a Secretar\u00eda Acad\u00e9mica para que te la den de alta.';
			}
		}).catch(function () {
			boton.disabled = false;
			boton.textContent = 'Mandame el mail para activarla';
			salida.className = 'hc-resultado hc-error';
			salida.textContent = 'No se pudo mandar el mail. Prob\u00e1 de nuevo en un rato.';
		});
	});
})();
</script>
JS;
	}

	protected function generar_layout_toba()
	{
		if ($this->existe_dependencia('seleccion_usuario')) {
			$this->dep('seleccion_usuario')->generar_html();
		}
		echo '<div>';
		if ($this->existe_dependencia('datos')) {
			echo "<div style='float:left;'>";
			$this->dep('datos')->generar_html();
			echo '</div>';
		}
		if ($this->existe_dependencia('openid')) {
			echo "<div style='padding-left: 30px; padding-right: 30px;float:right;'>";
			$this->dep('openid')->generar_html();
			echo '</div>';
		}
		if ($this->existe_dependencia('cas')) {
			echo "<div style='padding-left: 30px; padding-right: 30px;float:right;'>";
			$this->dep('cas')->generar_html();
			echo '</div>';
		}
		echo '</div>';
	}

	/** "31 de enero de 2027" */
	protected static function fecha_larga(DateTimeInterface $f)
	{
		$meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
			'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
		return $f->format('j') . ' de ' . $meses[(int)$f->format('n') - 1] . ' de ' . $f->format('Y');
	}

	/**
	 * Estilos de la pantalla Huayca. Copian los tokens de las pantallas de
	 * cuenta de lenga-crub (frontend/src/index.css, "Pantallas de cuenta") para
	 * que quien viene de la landing o de activar la cuenta sienta que sigue en el
	 * mismo lugar. Lo de Toba que rodea al formulario (barra, logo, titulo del
	 * ci, anchos fijos) se esconde o se pisa solo en esta pagina.
	 */
	protected function estilos_huayca()
	{
		return <<<CSS
<style>
/* La misma familia que la landing, Quintral (frontend/src/index.css, "Pantallas
   de cuenta") y la pantalla de la cuenta Huayca en Keycloak
   (lenga-crub/deploy/keycloak-tema): campo azul con el degrade de la landing,
   la marca, una tarjeta de 400px, campos y botones de 46px. */
:root {
	--hc-azul: #2563eb; --hc-azul-fuerte: #1d4ed8; --hc-azul-suave: #eff6ff;
	--hc-texto: #0f172a; --hc-texto-2: #334155; --hc-tenue: #64748b;
	--hc-borde: #cbd5e1; --hc-borde-suave: #e2e8f0; --hc-peligro: #b91c1c;
	--hc-radio: 16px; --hc-radio-campo: 10px;
	--hc-sombra: 0 1px 2px rgba(15,23,42,.08), 0 8px 24px rgba(15,23,42,.12);
}
html, body { background: var(--hc-azul) !important; margin: 0; }
body { font-family: system-ui, -apple-system, "Segoe UI", Ubuntu, Roboto, "Helvetica Neue", Arial, sans-serif !important; -webkit-font-smoothing: antialiased; }
.hc-pagina { color: #fff; }
/* Lo de Toba alrededor: barra, logo, titulo, y su pie "Desarrollado por SIU-CIN"
   (quedaba debajo de la pagina y obligaba a hacer scroll) */
#barra-superior, .barra-superior-login, .login-titulo, .login-pie { display: none !important; }
html body .cuerpo { padding: 0 !important; margin: 0 !important; overflow: visible; }
/* El contenedor del ci: sin marco. huayca.css le pone borde y fondo a la tarjeta
   de las operaciones (.cuerpo > form > table.ci-base) y aca se veia como un
   marco de 1px alrededor de toda la pagina. */
html body .cuerpo .ci-base, html body .ci-base > tbody > tr > td, html body .ci-cuerpo, html body .ci-simple-cont {
	background: transparent !important; border: 0 !important; border-radius: 0 !important; padding: 0 !important;
	width: 100% !important; min-width: 0 !important; margin: 0 !important; box-shadow: none !important; }
.ci-base > tbody > tr > td > .ei-barra-sup { display: none !important; }

/* Toda la altura: el bloque central se centra y la franja institucional queda al pie */
.hc-pagina { font-size: 16px; line-height: 1.5; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; }
.hc-centro {
	flex: 1; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center;
	gap: 28px; padding: 48px 16px 56px;
	background:
		radial-gradient(1200px 600px at 15% -10%, rgba(255,255,255,.14), transparent 60%),
		radial-gradient(900px 500px at 110% 110%, rgba(30,58,138,.55), transparent 60%),
		var(--hc-azul);
}
.hc-pagina .hy-pie { margin-top: 0; background: #fff; border-top: 0; text-align: left; }

/* La marca */
.hc-marca, .hc-marca div { color: #fff !important; }
.hc-marca { display: flex; align-items: center; gap: 18px; max-width: 100%; text-align: left; }
.hc-sello { width: clamp(68px, 9vw, 88px); height: auto; flex: none; }
.hc-nombre { font-size: clamp(34px, 5.5vw, 46px); font-weight: 500; line-height: 1.02; letter-spacing: .2px; }
.hc-bajada { margin-top: 6px; font-size: clamp(15px, 2vw, 17px); color: rgba(255,255,255,.88) !important; }

/* La tarjeta */
.hc-tarjeta { width: 100%; max-width: 400px; box-sizing: border-box; background: #fff; color: var(--hc-texto); border-radius: var(--hc-radio); padding: 28px 28px 24px; box-shadow: var(--hc-sombra); text-align: left; }
.hc-cabeza { margin-bottom: 18px; }
.hc-titulo { margin: 0; font-size: 22px; font-weight: 600; line-height: 1.25; color: var(--hc-texto); }
.hc-destino { margin: 2px 0 0; font-size: 15px; color: var(--hc-tenue); }
.hc-destino strong { color: var(--hc-texto-2); }
.hc-intro { margin: 12px 0 0; font-size: 15px; color: var(--hc-texto-2); }
.hc-intro a { color: var(--hc-azul-fuerte); font-weight: 600; }

/* El boton principal */
.hc-boton {
	display: flex; align-items: center; justify-content: center; box-sizing: border-box; width: 100%;
	min-height: 46px; padding: 0 16px; font-size: 16px; font-weight: 600;
	color: #fff !important; background: var(--hc-azul); border-radius: var(--hc-radio-campo);
	text-decoration: none !important; transition: background-color .15s ease;
}
.hc-boton:hover { background: var(--hc-azul-fuerte); }
.hc-boton:focus-visible { outline: 3px solid rgba(37,99,235,.35); outline-offset: 2px; }

/* Activar la cuenta: lo segundo mas visible */
.hc-activar { margin: 16px 0 0; font-size: 15px; color: var(--hc-tenue); text-align: center; }
.hc-activar a { color: var(--hc-azul-fuerte); font-weight: 600; text-decoration: none; }
.hc-activar a:hover { text-decoration: underline; text-underline-offset: 3px; }

/* La cuenta vieja: plegada detras de un enlace chico */
.hc-viejo { margin: 20px 0 0; padding-top: 16px; border-top: 1px solid var(--hc-borde-suave); }
.hc-viejo > summary { list-style: none; cursor: pointer; text-align: center; border-radius: 8px; padding: 4px; }
.hc-viejo > summary::-webkit-details-marker { display: none; }
.hc-viejo > summary:focus-visible { outline: 2px solid var(--hc-azul); outline-offset: 2px; }
.hc-viejo-tit { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 500; color: var(--hc-texto-2); }
.hc-viejo-tit::after {
	content: ''; width: 7px; height: 7px; margin-top: -3px;
	border-right: 1.5px solid currentColor; border-bottom: 1.5px solid currentColor;
	transform: rotate(45deg); transition: transform .15s ease;
}
.hc-viejo[open] .hc-viejo-tit::after { transform: rotate(-135deg); margin-top: 3px; }
.hc-viejo > summary:hover .hc-viejo-tit { color: var(--hc-texto); }
.hc-viejo-plazo { display: block; margin-top: 2px; font-size: 13px; color: var(--hc-tenue); }
.hc-viejo[open] > summary { margin-bottom: 14px; }
.hc-viejo-nota { margin: 0 0 16px; font-size: 14px; color: var(--hc-tenue); }

/* El formulario de Toba, adentro de la tarjeta */
.hc-tarjeta table.ei-base, .hc-tarjeta table.ei-base > tbody > tr > td { width: 100% !important; border: 0 !important; background: none !important; padding: 0 !important; }
.hc-tarjeta .ei-cuerpo { width: auto !important; background: none !important; border: 0 !important; padding: 0 !important; }
.hc-tarjeta .ei-barra-sup { display: none !important; }
.hc-tarjeta .ei-form-fila { display: block; margin: 0 0 16px; padding: 0; border: 0; background: none; }
.hc-tarjeta .ei-form-fila label { display: block; width: auto !important; float: none !important; text-align: left !important; margin: 0 0 6px; padding: 0; font-size: 15px; font-weight: 600; color: var(--hc-texto); }
.hc-tarjeta .ei-form-fila label + div { margin-left: 0 !important; }
.hc-tarjeta .ef-input, .hc-tarjeta input[type=text], .hc-tarjeta input[type=password] {
	width: 100% !important; box-sizing: border-box; min-height: 46px !important; padding: 10px 12px !important;
	font: inherit; font-size: 16px !important; color: var(--hc-texto); background: #fff;
	border: 1px solid var(--hc-borde); border-radius: var(--hc-radio-campo); box-shadow: none;
	transition: border-color .15s ease, box-shadow .15s ease;
}
.hc-tarjeta .ef-input:hover { border-color: #94a3b8; }
.hc-tarjeta .ef-input:focus { outline: none; border-color: var(--hc-azul); box-shadow: 0 0 0 3px rgba(37,99,235,.22); }
.hc-tarjeta .ei-botonera { padding: 0 !important; margin: 8px 0 0; background: none !important; border: 0 !important; text-align: left; }
/* El boton del formulario de Toba es secundario: el principal es el de Huayca */
.hc-tarjeta .ei-boton {
	width: 100%; min-height: 46px !important; margin: 0; padding: 0 16px !important; justify-content: center;
	font: inherit; font-size: 16px !important; font-weight: 600;
	color: var(--hc-azul-fuerte) !important; background: #fff !important;
	border: 1px solid var(--hc-borde) !important; border-radius: var(--hc-radio-campo);
	cursor: pointer; box-shadow: none !important; text-shadow: none !important;
}
.hc-tarjeta .ei-boton:hover { background: var(--hc-azul-suave) !important; border-color: #93c5fd !important; }
.hc-tarjeta .ei-boton u { text-decoration: none; }
.hc-tarjeta [id\$='_notificacion'] { color: var(--hc-peligro); font-size: 15px; }
.hc-tarjeta .ef-clave-fuerza, .hc-tarjeta [id\$='_fuerza'] { display: none !important; }
.hc-separador { display: flex; align-items: center; gap: 12px; margin: 24px 0 16px; font-size: 14px; color: var(--hc-tenue); }
.hc-separador::before, .hc-separador::after { content: ''; flex: 1; border-top: 1px solid var(--hc-borde-suave); }

/* Despues de entrar con la clave vieja */
.hc-plazo { display: inline-block; margin: 0 0 10px; padding: 3px 10px; font-size: 13px; font-weight: 600; color: var(--hc-azul-fuerte); background: var(--hc-azul-suave); border-radius: 999px; }
.hc-urgente .hc-plazo { color: #9a3412; background: #ffedd5; }
.hc-urgente .hc-titulo { color: #9a3412; }
.hc-pasaje .hc-intro { margin: 10px 0 20px; }
.hc-pasaje .hc-boton { font-family: inherit; border: 0; cursor: pointer; }
.hc-pasaje .hc-boton:disabled { cursor: default; opacity: .8; }
.hc-resultado { margin: 14px 0 0; padding: 10px 12px; font-size: 15px; line-height: 1.45; border-radius: var(--hc-radio-campo); }
.hc-resultado:empty { display: none; }
.hc-resultado.hc-ok { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
.hc-resultado.hc-error { color: var(--hc-peligro); background: #fef2f2; border: 1px solid #fecaca; }
.hc-otras { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: 18px; font-size: 14px; }
.hc-otras a { color: var(--hc-tenue); text-decoration: none; }
.hc-otras a:hover { color: var(--hc-texto); text-decoration: underline; text-underline-offset: 3px; }
.hc-otras a:first-child { color: var(--hc-azul-fuerte); font-weight: 600; font-size: 15px; }

/* Los enlaces de abajo, sobre el azul */
.hc-enlaces { display: flex; flex-direction: column; align-items: center; gap: 4px; margin-top: -12px; }
.hc-enlaces a { color: rgba(255,255,255,.92) !important; font-size: 14px; padding: 4px 8px; border-radius: 6px; text-decoration: none; }
.hc-enlaces a:hover { color: #fff !important; text-decoration: underline; text-underline-offset: 3px; }

@media (max-width: 560px) {
	.hc-centro { justify-content: flex-start; padding: 32px 16px 40px; }
	.hc-marca { flex-direction: column; gap: 10px; text-align: center; }
	.hc-tarjeta { padding: 22px 20px 20px; }
	.hc-pagina .hy-pie { padding: 20px 16px; }
	.hc-pagina .hy-pie-inst img { height: 44px; }
}
@media (prefers-reduced-motion: reduce) {
	.hc-boton, .hc-tarjeta .ef-input, .hc-viejo-tit::after { transition: none; }
}
</style>
CSS;
	}
}
?>
