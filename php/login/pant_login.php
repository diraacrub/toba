<?php
/**
 * Pantalla de login de catedras.
 *
 * Con la cuenta Huayca activa (instalacion/keycloak.ini) se ve como las
 * pantallas de cuenta de Huayca (Quintral, deptos): campo azul, el sello,
 * "/huayca", la tarjeta blanca con DNI y contraseña, y abajo los enlaces para
 * activar la cuenta o recuperar la contraseña. El formulario sigue siendo el de
 * Toba (sus efs, su JS, el token CSRF): solo se lo envuelve y se lo reestila.
 *
 * Sin keycloak.ini queda el layout original de Toba.
 *
 * Archivo en ASCII: los acentos van como entidades HTML.
 */
class pant_login extends toba_ei_pantalla
{
	function generar_layout()
	{
		if (class_exists('catedras_autenticacion_keycloak', false) && catedras_autenticacion_keycloak::activa()
				&& $this->existe_dependencia('datos')) {
			$this->generar_layout_huayca();
		} else {
			$this->generar_layout_toba();
		}
	}

	protected function generar_layout_huayca()
	{
		$esc = toba::escaper();
		$base = 'https://huayca.crub.uncoma.edu.ar/quintral';
		$activar = catedras_autenticacion_keycloak::param('url_activar', "$base/activate?app=catedras");
		$reset = catedras_autenticacion_keycloak::param('url_reset', "$base/reset-password?app=catedras");
		$sello = toba_recurso::imagen_proyecto('huayca-sello.png');

		echo $this->estilos_huayca();
		echo "<div class='hc-pagina'>";
		echo "<header class='hc-marca'>";
		echo "<img class='hc-sello' src='" . $esc->escapeHtmlAttr($sello) . "' alt='' aria-hidden='true'>";
		echo "<div><div class='hc-nombre'>/huayca</div><div class='hc-bajada'>Aplicaciones Digitales del CRUB</div></div>";
		echo "</header>";

		echo "<main class='hc-tarjeta'>";
		echo "<div class='hc-cabeza'>";
		echo "<h1 class='hc-titulo'>Iniciar sesi&oacute;n</h1>";
		echo "<p class='hc-destino'>para entrar a <strong>C&aacute;tedras</strong></p>";
		echo "<p class='hc-intro'>Con tu DNI y la contrase&ntilde;a de tu cuenta Huayca.</p>";
		echo "</div>";
		$this->dep('datos')->generar_html();
		echo "</main>";

		echo "<nav class='hc-enlaces'>";
		echo "<a href='" . $esc->escapeHtmlAttr($activar) . "'>&iquest;Primera vez? Activ&aacute; tu cuenta</a>";
		echo "<a href='" . $esc->escapeHtmlAttr($reset) . "'>&iquest;Olvidaste tu contrase&ntilde;a?</a>";
		echo "</nav>";

		echo "<footer class='hc-pie'>Centro Regional Universitario Bariloche &middot; Universidad Nacional del Comahue</footer>";
		echo "</div>";
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
:root {
	--hc-azul: #2563eb; --hc-azul-fuerte: #1d4ed8; --hc-texto: #111827; --hc-tenue: #475569;
	--hc-borde: #d1d5db; --hc-peligro: #b91c1c; --hc-radio: 12px;
	--hc-sombra: 0 6px 20px rgba(15, 23, 42, 0.12);
}
html, body { background: var(--hc-azul) !important; margin: 0; }
body { font-family: system-ui, Avenir, Helvetica, Arial, sans-serif !important; }
.hc-pagina { color: #fff; }
#barra-superior, .barra-superior-login, .login-titulo { display: none !important; }
.cuerpo { padding: 0 !important; margin: 0 !important; }
.ci-base, .ci-base > tbody > tr > td, .ci-cuerpo, .ci-simple-cont { background: transparent !important; border: 0 !important; padding: 0 !important; width: 100% !important; box-shadow: none !important; }
.ci-base > tbody > tr > td > .ei-barra-sup { display: none !important; }

.hc-pagina { font-size: 16px; line-height: 1.4; min-height: 100vh; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px; padding: 32px 16px; }
.hc-marca, .hc-marca div { color: #fff !important; }
.hc-marca { display: flex; align-items: center; gap: 16px; max-width: 100%; text-align: left; }
.hc-sello { width: clamp(64px, 9vw, 92px); height: auto; flex: none; }
.hc-nombre { font-size: clamp(30px, 5vw, 42px); line-height: 1.05; letter-spacing: .3px; }
.hc-bajada { margin-top: 2px; font-size: 15px; color: rgba(255,255,255,.85); }

.hc-tarjeta { width: 100%; max-width: 420px; box-sizing: border-box; background: #fff; color: var(--hc-texto); border-radius: var(--hc-radio); padding: 24px 24px 20px; box-shadow: var(--hc-sombra); text-align: left; }
.hc-cabeza { margin-bottom: 20px; }
.hc-titulo { margin: 0; font-size: 22px; font-weight: 600; color: var(--hc-texto); }
.hc-destino { margin: 2px 0 0; font-size: 15px; color: var(--hc-tenue); }
.hc-intro { margin: 12px 0 0; font-size: 15px; color: var(--hc-tenue); }

/* El formulario de Toba, adentro de la tarjeta */
.hc-tarjeta table.ei-base, .hc-tarjeta table.ei-base > tbody > tr > td { width: 100% !important; border: 0 !important; background: none !important; padding: 0 !important; }
.hc-tarjeta .ei-cuerpo { width: auto !important; background: none !important; border: 0 !important; padding: 0 !important; }
.hc-tarjeta .ei-barra-sup { display: none !important; }
.hc-tarjeta .ei-form-fila { display: block; margin: 0 0 16px; padding: 0; border: 0; background: none; }
.hc-tarjeta .ei-form-fila label { display: block; width: auto !important; float: none !important; text-align: left !important; margin: 0 0 6px; padding: 0; font-size: 15px; font-weight: 600; color: var(--hc-texto); }
.hc-tarjeta .ei-form-fila label + div { margin-left: 0 !important; }
.hc-tarjeta .ef-input, .hc-tarjeta input[type=text], .hc-tarjeta input[type=password] { width: 100% !important; box-sizing: border-box; min-height: 44px; padding: 10px 12px; font: inherit; font-size: 16px; color: var(--hc-texto); background: #fff; border: 1px solid var(--hc-borde); border-radius: 8px; box-shadow: none; }
.hc-tarjeta .ef-input:focus { outline: 2px solid var(--hc-azul); outline-offset: 1px; border-color: var(--hc-azul); }
.hc-tarjeta .ei-botonera { padding: 0 !important; margin: 8px 0 0; background: none !important; border: 0 !important; text-align: left; }
.hc-tarjeta .ei-boton { width: 100%; min-height: 44px; margin: 0; padding: 0 16px; font: inherit; font-size: 16px; font-weight: 600; color: #fff !important; background: var(--hc-azul) !important; border: 0 !important; border-radius: 8px; cursor: pointer; box-shadow: none !important; text-shadow: none !important; }
.hc-tarjeta .ei-boton:hover { background: var(--hc-azul-fuerte) !important; }
.hc-tarjeta .ei-boton u { text-decoration: none; }
.hc-tarjeta [id\$='_notificacion'] { color: var(--hc-peligro); font-size: 15px; }
.hc-tarjeta .ef-clave-fuerza, .hc-tarjeta [id\$='_fuerza'] { display: none !important; }

.hc-enlaces { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: -8px; }
.hc-enlaces a { color: #fff !important; font-size: 15px; padding: 4px 8px; border-radius: 6px; text-decoration: underline; text-underline-offset: 3px; }
.hc-enlaces a:hover { background: rgba(255,255,255,.14); }
.hc-pie { font-size: 13px; color: rgba(255,255,255,.85); text-align: center; }

@media (max-width: 560px) {
	.hc-pagina { justify-content: flex-start; padding: 24px 16px; }
	.hc-marca { flex-direction: column; gap: 8px; text-align: center; }
}
</style>
CSS;
	}
}
?>
