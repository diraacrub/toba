<?php
/**
 * Salida HTML propia de catedras: agrega el pie institucional a las paginas.
 *
 * Toba arma cada pagina con los componentes de toba::output() (un "manejador de
 * salida"). Aca se registra una fabrica que solo reemplaza PaginaNormal (la de
 * todas las operaciones con menu); el resto sigue con los de Toba. La registra
 * catedras_contexto_ejecucion::conf__inicial(). toba_editor no la usa (Toba
 * fuerza sus propios componentes para el editor).
 *
 * El pie va a continuacion del contenido (getPostContenido): el getFooterHtml()
 * de Toba se imprime despues de </body>, fuera del documento.
 *
 * Estilos: www/css/huayca.css, seccion "Pie". Archivo en ASCII: los acentos van
 * como entidades HTML.
 */

use SIU\InterfacesManejadorSalidaToba\IFactory;
use SIU\ManejadorSalidaToba\Componentes\TiposPagina\toba_tp_normal as siu_tp_normal;

class catedras_salida
{
	static function registrar()
	{
		toba::output()->registrarServicio(new catedras_factory_salida());
	}

	/** El pie, tambien lo usa la pantalla de login */
	static function pie_html()
	{
		$logo = toba::escaper()->escapeHtmlAttr(toba_recurso::imagen_proyecto('unco-bariloche.png'));
		return "<footer class='hy-pie'><div class='hy-pie-col'>"
			. "<div class='hy-pie-inst'>"
			. "<img src='$logo' alt='UNCo Bariloche' width='139' height='56'>"
			. "<p><strong>Centro Regional Universitario Bariloche</strong><br>Universidad Nacional del Comahue</p>"
			. "</div>"
			. "<p class='hy-pie-app'><strong>huayca/c&aacute;tedras</strong><br>"
			. "Plataforma de programas, planificaciones e informes del Centro Regional Universitario Bariloche</p>"
			. "</div></footer>";
	}
}

/**
 * La fabrica: solo los metodos de IFactory. Toba llama a TODOS sus metodos al
 * registrarla (los que devuelven null quedan con el componente de Toba), asi
 * que aca no puede haber ningun otro metodo.
 */
class catedras_factory_salida implements IFactory
{
	function getProvider() { return 'catedras'; }

	function getPaginaNormal() { return 'catedras_tp_normal_salida'; }

	// El resto: null = sigue el de Toba
	function getPaginaBasica() { return null; }
	function getPaginaTitulo() { return null; }
	function getPaginaPopup() { return null; }
	function getPaginaLogon() { return null; }
	function getMenu() { return null; }
	function getElementoInterfaz() { return null; }
	function getPantalla() { return null; }
	function getCuadro() { return null; }
	function getCuadroSalidaHtml() { return null; }
	function getFiltro() { return null; }
	function getFormulario() { return null; }
	function getFormularioMl() { return null; }
	function getEventoUsuario() { return null; }
	function getEventoTab() { return null; }
	function getInputsForm() { return null; }
	function getFiltroColumnas() { return null; }
}

class catedras_tp_normal_salida extends siu_tp_normal
{
	public function getPostContenido()
	{
		return parent::getPostContenido() . catedras_salida::pie_html();
	}
}
