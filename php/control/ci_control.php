<?php
class ci_control extends catedras_ci
{
	protected $s__datos_filtro;
// control sac ok
	

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
	$usuario_id         = toba::usuario()->get_id();
	$nombre_usuario     = toba::usuario()->get_nombre();
	$perfiles_funcionales = toba::usuario()->get_perfiles_funcionales();

	// --- Determinar la lista de deptos_principales en funci�n del perfil ---
	$deptos_principales = array(); // inicializa la lista

	// Si el array de perfiles funcionales contiene 'saccrub'
	if (in_array('saccrub', $perfiles_funcionales)) {
		// Para 'saccrub' la lista podr�a ser:
		$deptos_principales = array('ECOLOG�A', 'EXPLOTACI�N DE RECURSOS ACU�TICOS','MATEM�TICA', 'F�SICA', 'QU�MICA', 'BIOLOG�A GENERAL','BOT�NICA','EDUCACI�N F�SICA', 'DID�CTICA', 'ESTAD�STICA','ENFERMER�A','INGENIER�A CIVIL','POL�TICA EDUCACIONAL','PSICOLOG�A','GEOLOG�A Y PETR�LEO', 'ZOOLOG�A');
	}
	// Si el array de perfiles funcionales contiene 'sacfale'
	elseif (in_array('sacfadel', $perfiles_funcionales)) {
		// Para 'sacfale' la lista es:
		$deptos_principales = array('IDIOMAS EXTRANJEROS CON PROP�SITOS ESPEC�FICOS');
	}
	// Si se requieren otros casos, agr�galos aqu�.

	// --- Lista de usuarios que no requieren el filtro ---
	$excepciones = array('toba', 'vero', 'nacho');

	if (in_array($usuario_id, $excepciones)) {
		// Si el usuario es una excepci�n, obtener todos los datos
		if (isset($this->s__datos_filtro)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_excepciones($this->s__datos_filtro);
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control_excepciones();
		}
	} else {
		// Si no es una excepci�n, aplicar el filtro utilizando el perfil del usuario
		if (isset($this->s__datos_filtro)) {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control($this->s__datos_filtro,$usuario_id, $deptos_principales);
		} else {
			$datos = $this->dep('datos')->tabla('programas')->get_listado_control(array(),$usuario_id, $deptos_principales);
		}
	}
	
	// Verificamos que $datos sea un array antes de iterar
	if (is_array($datos)) {
		// Modificamos los estados para que se muestren de forma amigable
		foreach ($datos as $key => $registro) {
			if ($registro['estado'] === 'docente') {
				$datos[$key]['estado'] = 'Borrador';
			} elseif ($registro['estado'] === 'depto') {
				$datos[$key]['estado'] = 'En revisi�n del Departamento';
			} elseif ($registro['estado'] === 'sac') {
				$datos[$key]['estado'] = 'En revisi�n de la Secretar�a Acad�mica';
			}
		}
	}
	
	// Por �ltimo, se asignan los datos modificados al cuadro
	$cuadro->set_datos($datos);
		
	


	
		
	}


}
?>