<?php
class ci_control_depto extends catedras_ci
{

protected $s__datos_filtro;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
{
	$usuario_id     = toba::usuario()->get_id();
	$nombre_usuario = toba::usuario()->get_nombre();
	$perfil_usuario = toba::usuario()->get_perfiles_funcionales();
	
	// Lista de usuarios que no requieren el filtro
	$excepciones = array('toba', 'vero', 'nacho');
	
	if (in_array($usuario_id, $excepciones)) {
		// Si el usuario es una excepción, obtener todos los datos
		if (isset($this->s__datos_filtro)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_depto_excepciones($this->s__datos_filtro);
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_depto_excepciones();
		}
	} else {
		// Si no es una excepción, aplicar el filtro utilizando el perfil del usuario
		if (isset($this->s__datos_filtro)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_depto($this->s__datos_filtro,$perfil_usuario);
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_depto(array(),$perfil_usuario);
		}
	}
	
	// Verificamos que $datos sea un array antes de iterar
	if (is_array($datos)) {
		// Modificamos los estados para que se muestren de forma amigable
		foreach ($datos as $key => $registro) {
			if ($registro['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';
			} elseif ($registro['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisión del Departamento';
			} elseif ($registro['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';
			}
		}
	}
	
	// Por último, se asignan los datos modificados al cuadro
	$cuadro->set_datos($datos);
}

	
	
	
	function conf__cuadro_antes(toba_ei_cuadro $cuadro)
	{
		
		
		$usuario_id       = toba::usuario()->get_id();
		$nombre_usuario   = toba::usuario()->get_nombre();
		$perfil_usuario   = toba::usuario()->get_perfiles_funcionales(); // Usamos el perfil
	
		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			//$datos = $this->dep('datos')->tabla('programas')->get_listado_estado_sac_aprobado();
			if (isset($this->s__datos_filtro)) {
				$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_control_depto_excepciones($this->s__datos_filtro));
				} else {
				$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_control_depto_excepciones());
				}
						
			} else {
			// Si no es una excepción, aplicar el filtro utilizando el perfil del usuario
			//$datos = $this->dep('datos')->tabla('programas')->get_listado_enviados_depto($usuario_id, $perfil_usuario);
			if (isset($this->s__datos_filtro)) {
				$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_control_depto($this->s__datos_filtro));
				} else {
				$cuadro->set_datos($this->dep('datos')->tabla('programas')->get_listado_control_depto());
				}


			}

			// Modificamos los estados para que se muestren de forma amigable
		foreach ($datos as $key => $registro) {
		if ($registro['estado'] === 'docente') {
			$datos[$key]['estado'] = 'Borrador';
			}
		if ($registro['estado'] === 'depto') {
			$datos[$key]['estado'] = 'En revisión del Departamento';
			}
		if ($registro['estado'] === 'sac') {
			$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';
			}
		}
		
		
		////
		
	}

	
}
?>