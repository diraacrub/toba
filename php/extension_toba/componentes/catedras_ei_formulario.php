<?php
class catedras_ei_formulario extends toba_ei_formulario
{
	/**
	 * Secciones condicionales de la plantilla (template y template de
	 * impresion): lo que esta entre <!--seccion:nombre--> y
	 * <!--/seccion:nombre--> se muestra solo si el CI lo pide con
	 * mostrar_seccion('nombre'). Sirve para que los titulos escritos en la
	 * plantilla no queden sueltos cuando sus efs estan desactivados (p. ej.
	 * los campos de ingenierias en un programa de otra carrera).
	 */
	protected $_secciones_visibles = array();

	function mostrar_seccion($nombre, $mostrar = true)
	{
		$this->_secciones_visibles[$nombre] = (bool) $mostrar;
	}

	protected function filtrar_secciones($template)
	{
		if (!is_string($template) || strpos($template, '<!--seccion:') === false) {
			return $template;
		}
		$visibles = $this->_secciones_visibles;
		return preg_replace_callback('/<!--seccion:(\w+)-->(.*?)<!--\/seccion:\1-->/s',
			function ($m) use ($visibles) {
				return !empty($visibles[$m[1]]) ? $m[2] : '';
			}, $template);
	}

	protected function generar_layout_template()
	{
		$original = $this->_info_formulario['template'];
		$this->_info_formulario['template'] = $this->filtrar_secciones($original);
		try {
			parent::generar_layout_template();
		} catch (Exception $e) {
			$this->_info_formulario['template'] = $original;
			throw $e;
		}
		$this->_info_formulario['template'] = $original;
	}

	protected function generar_layout_template_impresion()
	{
		$original = $this->_info_formulario['template_impresion'];
		$this->_info_formulario['template_impresion'] = $this->filtrar_secciones($original);
		try {
			parent::generar_layout_template_impresion();
		} catch (Exception $e) {
			$this->_info_formulario['template_impresion'] = $original;
			throw $e;
		}
		$this->_info_formulario['template_impresion'] = $original;
	}
}
?>
