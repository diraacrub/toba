<?php
class ci_programas extends catedras_ci
{
	
	private $id_programa_seleccionado;
	
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado();
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_filtrado($usuario_id);
		}
		
		foreach ($datos as $key => $registro) {
			if ($registro ['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';        
			}
			if ($registro ['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';        
			}
		}
			
		
		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}

	
	////-------------------
		
	function evt__cuadro__seleccion($datos)
	{
		$this->id_programa_seleccionado = $datos['id_programa']; // Guardar el ID del programa seleccionado
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}

	
	/// Cuadro enviados///
	
		function conf__enviados(catedras_ei_cuadro $cuadro)
	{
		$usuario_id = toba::usuario()->get_id();
		$nombre_usuario = toba::usuario()->get_nombre();
		$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();
		toba::logger()->info("Perfiles funcionales del usuario ($usuario_id): " . implode(', ', $perfiles_funcionales));

		// Lista de usuarios que no requieren el filtro
		$excepciones = array('toba', 'vero', 'nacho');

		if (in_array($usuario_id, $excepciones)) {
			// Si el usuario es una excepción, obtener todos los datos
			$datos = $this->dep('datos')->tabla('programas')->get_listado();
		} else {
				// Si no es una excepción, aplicar el filtro por legajo_resp y estado
			$datos = $this->dep('datos')->tabla('programas')->get_listado_enviados($usuario_id);
		}
		foreach ($datos as $key => $registro) {
			if ($registro ['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';        
			}
			if ($registro ['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisión del Departamento';        
			}
			if ($registro ['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisión de la Secretaría Académica';        
			}
		}
			

		$cuadro->set_datos($datos);
		toba::logger()->info("Usuario ID: $usuario_id");
		toba::logger()->info("Nombre del usuario: $nombre_usuario");
	}

	// ************ SEGUNDA PANTALLA ************************
	
		
	//// ---- Cuadro Materias --------
	
	function conf__materia(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	
	
	//// ---- Cuadro correlatividades --------
	
	function conf__correlatividades(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	//// ---- Cuadro carrera --------
	
	function conf__carrera(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	
	//// ---- Cuadro Datos del Responsable --------
	
	function conf__datos_resp(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	//// ---- Cuadro Contenidos minimos y Competencias --------
	
	function conf__contenidos_minimos(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	
		//// ---- Cuadro Año Academico --------
	
	function conf__ano_acad(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}
	
		//// ---- Cuadro Nombre Materia --------
	
	function conf__nom_mat(catedras_ei_cuadro $cuadro)
	{
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Asegurarse de que los datos se pasan en un arreglo de dos dimensiones
		if ($datos_programa) {
			$cuadro->set_datos(array($datos_programa)); // Encapsula en un arreglo para que sea un arreglo de 2 dimensiones
		} else {
			$cuadro->set_datos(array()); // Si no hay datos, se pasa un arreglo vacío
		}
	}    
	
	
	
	
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);
			if ($datos_programa) {
				$form->set_datos($datos_programa);
			}
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	//---- Modificación del Formulario --------------------------------------------------
	function evt__formulario__modificacion($datos)
	{
		// Obtener el comentario ingresado por el usuario
		$comentario = isset($datos['comentario']) ? $datos['comentario'] : '';

		if (!empty($comentario)) {
			// Obtener el timestamp y el nombre del usuario
			$timestamp = date('Y-m-d H:i:s');
			$usuario_id = toba::usuario()->get_id();
			$nombre_completo = toba::usuario()->get_nombre();

			// Obtener el perfil funcional del usuario
			$perfil_funcional = toba::usuario()->get_perfiles_funcionales();

			// Definir los perfiles funcionales que quieres colorear
			$perfiles_colorear = array('docente', 'departamento', 'sac'); // Usamos array() en lugar de []

			// Determinar el color basado en el perfil funcional
			$color_fondo = '#f0f0f0';  // Color por defecto (gris claro)

			// Verificar si alguno de los perfiles funcionales del usuario coincide
			if (array_intersect($perfiles_colorear, $perfil_funcional)) {
				if (in_array('docente', $perfil_funcional)) {
					$color_fondo = '#d4edda';  // Verde claro para Docente
				} elseif (in_array('departamento', $perfil_funcional)) {
					$color_fondo = '#cce5ff';  // Azul claro para Departamento
				} elseif (in_array('sac', $perfil_funcional)) {
					$color_fondo = '#ffe5b4';  // Naranja claro para Secretaría Académica
				}
			}
			// Crear el HTML del comentario
			$nuevo_comentario = "<div style='border: 1px solid #ccc; padding: 10px; margin-top: 10px; background-color: $color_fondo; color: #333; font-family: Arial, sans-serif;'>
									<strong>$nombre_completo ($usuario_id) - $timestamp</strong><br>
									<p style='margin: 5px 0;'>$comentario</p>
									</div>";

			// Obtener el contenido existente del campo comentarios
			$comentarios = $this->dep('datos')->tabla('programas')->get_columna('comentarios');

			// Concatenar el nuevo comentario al contenido existente
			$nuevo_comentarios = $comentarios . $nuevo_comentario;

			// Asignar el nuevo valor a la columna comentarios
			$datos['comentarios'] = $nuevo_comentarios;
		}

		$this->dep('datos')->tabla('programas')->set($datos);
	}
	
//-----------------------------------------------------------------------------------------------    
	
	
	
	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->resetear();
	}

	function evt__eliminar()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__guardar()
	{
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}









	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__imprimir()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__imprimir = function()
		{
		}
		";
	}

}
?>