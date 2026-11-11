<?php
/**
 * Salida HTML propia de catedras: el tema y el pie institucional.
 *
 * Toba arma cada pagina con los componentes de toba::output() (un "manejador de
 * salida"). Aca se registra una fabrica que reemplaza dos; el resto sigue con
 * los de Toba. La registra catedras_contexto_ejecucion::conf__inicial().
 * toba_editor no la usa (Toba fuerza sus propios componentes para el editor).
 *
 * - PaginaBasica (el <head> de todas las paginas, login incluido): enlaza
 *   www/css/huayca.css con ?v=<fecha del archivo>. Antes lo traia un @import
 *   desde css/toba.css, pero Toba enlaza ese archivo siempre con ?av=3.4.6 y el
 *   servidor no manda Cache-Control: el navegador seguia con el CSS viejo
 *   despues de un deploy. Asi cada cambio de huayca.css es una URL nueva.
 * - PaginaNormal (las operaciones con menu): el pie, a continuacion del
 *   contenido (getPostContenido); el getFooterHtml() de Toba se imprime despues
 *   de </body>, fuera del documento.
 *
 * Estilos: www/css/huayca.css, seccion "Pie". Archivo en ASCII: los acentos van
 * como entidades HTML.
 */

use SIU\InterfacesManejadorSalidaToba\IFactory;
use SIU\ManejadorSalidaToba\Componentes\TiposPagina\toba_tp_normal as siu_tp_normal;
use SIU\ManejadorSalidaToba\Componentes\TiposPagina\toba_tp_basico as siu_tp_basico;

class catedras_salida
{
	static function registrar()
	{
		toba::output()->registrarServicio(new catedras_factory_salida());
	}

	/** <link> al tema, versionado por la fecha del archivo */
	static function link_tema()
	{
		$archivo = __DIR__ . '/../../www/css/huayca.css';
		$v = is_file($archivo) ? filemtime($archivo) : 0;
		$url = toba::escaper()->escapeHtmlAttr(toba_recurso::url_proyecto() . "/css/huayca.css?v=$v");
		return "<link rel='stylesheet' type='text/css' media='screen' href='$url'/>\n";
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

	function getPaginaBasica() { return 'catedras_tp_basico_salida'; }
	function getPaginaNormal() { return 'catedras_tp_normal_salida'; }

	// El resto: null = sigue el de Toba
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

class catedras_tp_basico_salida extends siu_tp_basico
{
	// Despues de los CSS de Toba y del proyecto (css/toba.css): el tema les gana
	public function getPlantillasCss()
	{
		return parent::getPlantillasCss() . catedras_salida::link_tema();
	}
}

class catedras_tp_normal_salida extends siu_tp_normal
{
	public function getPostContenido()
	{
		return parent::getPostContenido() . catedras_salida::pie_html();
	}
}
