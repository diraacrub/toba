<?php
class ci_programas extends catedras_ci

{
	
	private $id_programa_seleccionado;
	

	function vista_pdf(toba_vista_pdf $salida)
{
	
	parent::vista_pdf($salida);
		
}

	
	
	
		
	
function conf__formulario_con_todo(toba_ei_formulario $form)
{
	// Verificar si los datos están cargados
	if ($this->dep('datos')->esta_cargada()) {
		// Obtener los datos del programa
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Setear de solo lectura
		$form->set_solo_lectura(array('contenidos_minimos'), true);
		
		
		// Comprobar si los datos del programa se obtuvieron correctamente
		if ($datos_programa) {
			// Establecer los datos en el formulario
			$form->set_datos($datos_programa);

			// Definir carreras que no muestran horas teóricas prácticas
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			// Verificar si la carrera pertenece a las que están en el array
			if (in_array($datos_programa['cod_carrera'], $carreras_coneau)) {
				// Si la carrera está en el array, hacer el campo "horas_teoricopracticas" solo lectura
				$form->set_solo_lectura(array('horas_teoricopracticas'), true);
			} else {
				// Si la carrera no está en el array, asegurarse de que el campo sea editable
				$form->set_solo_lectura(array('horas_teoricopracticas'), false);
			}
		}
	} else {
		// Si no hay datos cargados, eliminar el evento de eliminar
		$this->pantalla()->eliminar_evento('eliminar');
	}
}

function evt__formulario_con_todo__modificacion($datos) {
	// Obtener las horas
	$horas_totales = isset($datos['horas_totales']) ? $datos['horas_totales'] : 0;
	$horas_practicas = isset($datos['horas_practicas']) ? $datos['horas_practicas'] : 0;
	$horas_teoricas = isset($datos['horas_teoricas']) ? $datos['horas_teoricas'] : 0;
	$horas_teoricopracticas = isset($datos['horas_teoricopracticas']) ? $datos['horas_teoricopracticas'] : 0;

	// Calcular el subtotal de horas
	$subtotal_horas = $horas_practicas + $horas_teoricas + $horas_teoricopracticas;

	// Validar si las horas coinciden (opcional, puedes omitirlo si lo haces en el evento de salida)
	if ($subtotal_horas != $horas_totales) {
		throw new toba_error("La suma de horas teóricas, prácticas y teorico-prácticas no coincide con la carga horaria total por plan de estudios, por favor corrija.");
						
	}

	// Manejo de comentarios
	$comentario = isset($datos['comentario']) ? $datos['comentario'] : '';
	if (!empty($comentario)) {
		// Obtener el timestamp y el nombre del usuario
		$timestamp = date('Y-m-d H:i:s');
		$usuario_id = toba::usuario()->get_id();
		$nombre_completo = toba::usuario()->get_nombre();
		
		// Crear el HTML del comentario
		$nuevo_comentario = "<div style='border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
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

	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar al guardar correctamente
	toba::notificacion()->agregar("Su programa ha sido guardado correctamente", 'info');

	// Refrescar el formulario para mostrar el nuevo subtotal
	$this->dep('datos')->tabla('programas')->sincronizar();
}

	
	
	
	
	//---- Modificación del Formulario --------------------------------------------------
function evt__formulario__modificacion($datos) {
	// Obtener las horas
	$horas_totales = isset($datos['horas_totales']) ? $datos['horas_totales'] : 0;
	$horas_practicas = isset($datos['horas_practicas']) ? $datos['horas_practicas'] : 0;
	$horas_teoricas = isset($datos['horas_teoricas']) ? $datos['horas_teoricas'] : 0;
	$horas_teoricopracticas = isset($datos['horas_teoricopracticas']) ? $datos['horas_teoricopracticas'] : 0;

	// Calcular el subtotal de horas
	$subtotal_horas = $horas_practicas + $horas_teoricas + $horas_teoricopracticas;

	// Validar si las horas coinciden (opcional, puedes omitirlo si lo haces en el evento de salida)
	if ($subtotal_horas != $horas_totales) {
		throw new toba_error("La suma de horas no coincide con la carga horaria total por plan de estudios, por favor corrija.");
						
	}

	// Manejo de comentarios
	$comentario = isset($datos['comentario']) ? $datos['comentario'] : '';
	if (!empty($comentario)) {
		// Obtener el timestamp y el nombre del usuario
		$timestamp = date('Y-m-d H:i:s');
		$usuario_id = toba::usuario()->get_id();
		$nombre_completo = toba::usuario()->get_nombre();
		
		// Crear el HTML del comentario
		$nuevo_comentario = "<div style='border: 1px solid #ccc; padding: 10px; margin-top: 10px;'>
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

	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar el subtotal de horas
	toba::notificacion()->agregar("Subtotal de horas: $subtotal_horas, Total según plan: $horas_totales", 'info');

	// Refrescar el formulario para mostrar el nuevo subtotal
	$this->dep('datos')->tabla('programas')->sincronizar();
}

	
//------------------------------
	
	function evt__formulario__modificacion_2($datos)
	{
	
	// Obtener los valores de horas directamente
	$horas_totales = isset($datos['horas_totales']) ? $datos['horas_totales'] : 0;
	$horas_practicas = isset($datos['horas_practicas']) ? $datos['horas_practicas'] : 0;
	$horas_teoricas = isset($datos['horas_teoricas']) ? $datos['horas_teoricas'] : 0;
	$horas_teoricopracticas = isset($datos['horas_teoricopracticas']) ? $datos['horas_teoricopracticas'] : 0;

	// Calcular el subtotal de horas
	$subtotal_horas = $horas_practicas + $horas_teoricas + $horas_teoricopracticas;

	// Comparar subtotal_horas con horas_totales
	if ($subtotal_horas != $horas_totales) {
		
		throw new toba_error("La suma de horas no coincide con la carga horaria total por plan de estudios, por favor corrija.");
		
		// Mostrar un mensaje de error
		//toba::notificacion()->agregar("La suma de horas no coincide con la carga horaria total por plan de estudios, por favor corrija.", 'error');

		
		// Sincronizar el formulario para mantener el estado actual
		//  $this->dep('datos')->tabla('programas')->sincronizar();
		
		// No guardar datos, salir de la función
		//return;
		}
		else {
		// Aquí puedes continuar con el proceso de modificación
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
			$perfiles_colorear = array('docente', 'departamento', 'sac');

			// Determinar el color basado en el perfil funcional
			$color_fondo = '#f0f0f0'; // Color por defecto (gris claro)

			// Verificar si alguno de los perfiles funcionales del usuario coincide
			if (array_intersect($perfiles_colorear, $perfil_funcional)) {
				if (in_array('docente', $perfil_funcional)) {
					$color_fondo = '#d4edda'; // Verde claro para Docente
				} elseif (in_array('departamento', $perfil_funcional)) {
					$color_fondo = '#cce5ff'; // Azul claro para Departamento
				} elseif (in_array('sac', $perfil_funcional)) {
					$color_fondo = '#ffe5b4'; // Naranja claro para Secretaría Académica
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

			
	// Guardar los datos modificados en la tabla 'programas'
	$this->dep('datos')->tabla('programas')->set($datos);

	// Agregar una notificación para mostrar el subtotal de horas
	toba::notificacion()->agregar("Subtotal de horas: $subtotal_horas, Total según plan: $horas_totales", 'info');

	// Refrescar el formulario para mostrar el nuevo subtotal
	$this->dep('datos')->tabla('programas')->sincronizar();
	}
}
	
	
	
	
function conf__formulario(toba_ei_formulario $form)
{
	// Verificar si los datos están cargados
	if ($this->dep('datos')->esta_cargada()) {
		// Obtener los datos del programa
		$datos_programa = $this->dep('datos')->tabla('programas')->get_datos_programa($this->id_programa_seleccionado);

		// Comprobar si los datos del programa se obtuvieron correctamente
		if ($datos_programa) {
			// Establecer los datos en el formulario
			$form->set_datos($datos_programa);

			// Definir carreras que no muestran horas teóricas prácticas
			$carreras_coneau = array('LBIB', 'LENB', 'ICIB', 'IELB', 'IETB', 'IMEB', 'IPEB', 'IQUB');

			// Verificar si la carrera pertenece a las que están en el array
			if (in_array($datos_programa['cod_carrera'], $carreras_coneau)) {
				// Si la carrera está en el array, hacer el campo "horas_teoricopracticas" solo lectura
				$form->set_solo_lectura(array('horas_teoricopracticas'), true);
			} else {
				// Si la carrera no está en el array, asegurarse de que el campo sea editable
				$form->set_solo_lectura(array('horas_teoricopracticas'), false);
			}
		}
	} else {
		// Si no hay datos cargados, eliminar el evento de eliminar
		$this->pantalla()->eliminar_evento('eliminar');
	}
}

	
	//---- CUADROS
	
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






function vista_impresion( toba_impresion $salida )
	{
		
		$this->dep('formulario_con_todo')->vista_impresion($salida);
		
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
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__guardar = function()
		{
		}
		";
	}

	

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------


}
?>