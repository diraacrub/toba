<?php
class dt_programas extends catedras_datos_tabla
{

	function get_listado()
	{
		$sql = "SELECT
			t_p.*,
			t_m.*

		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia
		ORDER BY dni_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	function get_listado_abm_programas_filtrado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_programa'])) {
			$where[] = "id_programa = ".quote($filtro['id_programa']);
		}
		if (isset($filtro['legajo_resp'])) {
			$where[] = "legajo_resp = ".quote($filtro['legajo_resp']);
		}
		if (isset($filtro['apellido_resp'])) {
			$where[] = "apellido_resp ILIKE ".quote("%{$filtro['apellido_resp']}%");
		}
		if (isset($filtro['cargo_resp'])) {
			$where[] = "cargo_resp ILIKE ".quote("%{$filtro['cargo_resp']}%");
		}
		if (isset($filtro['id_materia_prog'])) {
			$where[] = "id_materia_prog = ".quote($filtro['id_materia_prog']);
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado ILIKE ".quote("%{$filtro['periodo_dictado']}%");
		}
		if (isset($filtro['ano_academico'])) {
			$where[] = "ano_academico ILIKE ".quote("%{$filtro['ano_academico']}%");
		}
		if (isset($filtro['estado'])) {
			$where[] = "estado ILIKE ".quote("%{$filtro['estado']}%");
		}
		$sql = "SELECT
			t_p.*,
			t_m.*

		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia
		ORDER BY dni_resp";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}


	// listado control de sac
	
	function get_listado_control($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_programa'])) {
			$where[] = "id_programa = ".quote($filtro['id_programa']);
		}
		if (isset($filtro['id_designacion'])) {
			$where[] = "id_designacion = ".quote($filtro['id_designacion']);
		}
		if (isset($filtro['id_asignacion'])) {
			$where[] = "id_asignacion = ".quote($filtro['id_asignacion']);
		}
		if (isset($filtro['legajo_resp'])) {
			$where[] = "legajo_resp = ".quote($filtro['legajo_resp']);
		}
		if (isset($filtro['dni_resp'])) {
			$where[] = "dni_resp = ".quote($filtro['dni_resp']);
		}
		if (isset($filtro['apellido_resp'])) {
			$where[] = "apellido_resp ILIKE ".quote("%{$filtro['apellido_resp']}%");
		}
		if (isset($filtro['nombre_resp'])) {
			$where[] = "nombre_resp ILIKE ".quote("%{$filtro['nombre_resp']}%");
		}
		if (isset($filtro['cargo_resp'])) {
			$where[] = "cargo_resp ILIKE ".quote("%{$filtro['cargo_resp']}%");
		}
		if (isset($filtro['equipo_catedra'])) {
			$where[] = "equipo_catedra ILIKE ".quote("%{$filtro['equipo_catedra']}%");
		}
		if (isset($filtro['id_materia_prog'])) {
			$where[] = "id_materia_prog = ".quote($filtro['id_materia_prog']);
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado ILIKE ".quote("%{$filtro['periodo_dictado']}%");
		}
		if (isset($filtro['ano_academico'])) {
			$where[] = "ano_academico ILIKE ".quote("%{$filtro['ano_academico']}%");
		}
		if (isset($filtro['estado'])) {
			$where[] = "estado ILIKE ".quote("%{$filtro['estado']}%");
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = "nombre_materia ILIKE ".quote("%{$filtro['nombre_materia']}%");
		}
						
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia  
			AND
				t_p.estado IN ('docente', 'depto')    
		ORDER BY nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}


	// listados de control para deptos (uno para excepcines y el otro para los dptos)
	function get_listado_control_depto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_programa'])) {
			$where[] = "id_programa = ".quote($filtro['id_programa']);
		}
		if (isset($filtro['id_designacion'])) {
			$where[] = "id_designacion = ".quote($filtro['id_designacion']);
		}
		if (isset($filtro['id_asignacion'])) {
			$where[] = "id_asignacion = ".quote($filtro['id_asignacion']);
		}
		if (isset($filtro['legajo_resp'])) {
			$where[] = "legajo_resp = ".quote($filtro['legajo_resp']);
		}
		if (isset($filtro['dni_resp'])) {
			$where[] = "dni_resp = ".quote($filtro['dni_resp']);
		}
		if (isset($filtro['apellido_resp'])) {
			$where[] = "apellido_resp ILIKE ".quote("%{$filtro['apellido_resp']}%");
		}
		if (isset($filtro['nombre_resp'])) {
			$where[] = "nombre_resp ILIKE ".quote("%{$filtro['nombre_resp']}%");
		}
		if (isset($filtro['cargo_resp'])) {
			$where[] = "cargo_resp ILIKE ".quote("%{$filtro['cargo_resp']}%");
		}
		if (isset($filtro['equipo_catedra'])) {
			$where[] = "equipo_catedra ILIKE ".quote("%{$filtro['equipo_catedra']}%");
		}
		if (isset($filtro['id_materia_prog'])) {
			$where[] = "id_materia_prog = ".quote($filtro['id_materia_prog']);
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado ILIKE ".quote("%{$filtro['periodo_dictado']}%");
		}
		if (isset($filtro['ano_academico'])) {
			$where[] = "ano_academico ILIKE ".quote("%{$filtro['ano_academico']}%");
		}
		if (isset($filtro['estado'])) {
			$where[] = "estado ILIKE ".quote("%{$filtro['estado']}%");
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = "nombre_materia ILIKE ".quote("%{$filtro['nombre_materia']}%");
		}
						
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia  
			AND
				t_p.estado IN ('docente')    
		ORDER BY nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}


		
	

	//------------ get_listado de repuesto pos si se borra al realizar alguna acción
		function get_listado_de_repuesto()
	{
		$sql = "SELECT
			t_p.*,
			t_m.*

		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia
		ORDER BY dni_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	// todos los programas con estado docente
	function get_listado_estado_docente()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado = 'docente'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		
		
	}
	// todos los programas con estado depto
	function get_listado_estado_depto()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado = 'depto'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
		// todos los programas con estado sac
	
	function get_listado_estado_sac()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado = 'sac'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		
	}
		// todos los programas con estado aprobado
	function get_listado_estado_aprobado()
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado = 'aprobado'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);   
	}
	
	
	// todos los programas con estado depto, sac o aprobado
	function get_listado_estado_depto_sac_aprobado()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado IN ('depto', 'sac', 'aprobado')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	// todos los programas con estado sac o aprobado
	function get_listado_estado_sac_aprobado()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado IN ('sac', 'aprobado')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
		
	// todos los programas con estado docente o depto
	function get_listado_estado_docente_depto()
	{
			$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado IN ('docente', 'depto')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
			
	
	
	
	// --- metodo para imprimir programas aprobados para operacion publica
	
	
function get_listado_para_imprimir_publico($filtro = "")
{
	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas AS t_p,
				materias AS t_m
			WHERE
				t_p.id_materia_prog = t_m.id_materia
				AND t_p.estado = 'aprobado'";

	// Verifica si se ha proporcionado algún filtro válido
	if (is_array($filtro) && !empty($filtro)) {
		foreach ($filtro as $campo => $valor) {
			// Si el valor no está vacío, agrega la condición usando ILIKE con % para búsqueda parcial
			if ($valor !== '') {
				$sql .= " AND " . $campo . " ILIKE " . toba::db('catedras')->quote('%' . $valor . '%');
			}
		}
	}

	$sql .= " ORDER BY dni_resp";

	// Log para verificar la consulta SQL generada
	toba::logger()->info('Consulta SQL generada: ' . $sql);

	return toba::db('catedras')->consultar($sql);
}

	
	
	
	
	
		function get_listado_para_imprimir_publico_original()
	{
		$sql = "SELECT
			t_p.*,
			t_m.*

		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia
			AND
				t_p.estado = 'aprobado'
		ORDER BY dni_resp";
		return toba::db('catedras')->consultar($sql);
		
	}
	

	
	//---- Nuevo método para obtener datos específicos de un programa ----
	
	function get_datos_programa($id_programa)
{
	$sql = "SELECT
		t_p.*,
		t_m.*
	FROM
		programas as t_p
	JOIN
		materias as t_m ON t_p.id_materia_prog = t_m.id_materia
	WHERE
		t_p.id_programa = " . quote($id_programa);
	
	return toba::db('catedras')->consultar_fila($sql);
}

	
/// para DOCENTES
	
	///////  listado filtrado para docente
		function get_listado_filtrado($usuario_id)
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_p.estado = 'docente'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	//---- Nuevo método para obtener datos filtrados por estado para docente ----
	function get_listado_enviados($usuario_id)
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_p.estado IN ('depto', 'sac', 'aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}

	
//// para DEPTOS 

	
		///// listado filtrado para depto para firmar
function get_listado_filtrado_depto($usuario_id, $perfil_usuario)
{

$perfiles_validos = array(
	'biologiageneral',
	'botanica',
	'didactica',
	'ecologia',
	'educacionfisica',
	'enfermeria',
	'estadistica',
	'explotacionderecursosacuaticos',
	'fisica',
	'geologiaypetroleo',
	'idiomasextranjerosconpropositosespecificos',
	'ingenieriacivil',
	'matematica',
	'politicaeducacional',
	'psicologia',
	'quimica',
	'zoologia'
);

	
	// Se recorre el arreglo de perfiles funcionales para buscar uno de los perfiles válidos
	$perfil = '';
	foreach ($perfil_usuario as $perfil_item) {
		// Comparamos en minúsculas para evitar problemas de mayúsculas/minúsculas
		if (in_array(strtolower($perfil_item), $perfiles_validos)) {
			$perfil = $perfil_item;
			break;  // Se toma el primero que coincida
		}
	}
	
	if ($perfil === '') {
		throw new Exception("El perfil funcional no está definido.");
	}
	
	// Escapamos el valor para evitar inyección SQL (ajusta según tu framework)
	$perfilEscaped = pg_escape_string($perfil);
	// Construimos el literal SQL: se deben usar comillas simples para literales
	$perfilLiteral = "'" . $perfilEscaped . "'";
	
	/*
		Usamos translate() para eliminar acentos:
		- Primero aplicamos lower() a la columna y al literal para evitar diferencias de mayúsculas/minúsculas.
		- Luego, con translate(), reemplazamos las vocales acentuadas (por ejemplo: á, é, í, ó, ú y sus mayúsculas)
			por sus equivalentes sin acento.
	*/
	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas AS t_p
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
			WHERE
				replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
				ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
				AND t_p.estado = 'depto'
			ORDER BY
				nombre_materia";
	
	return toba::db('catedras')->consultar($sql);
}


	
	
	
	
		//---- listado para depto firmados
function get_listado_enviados_depto($usuario_id, $perfil_usuario)
{
	// Definir los perfiles válidos
	$perfiles_validos = array(
		'biologiageneral',
		'botanica',
		'didactica',
		'ecologia',
		'educacionfisica',
		'enfermeria',
		'estadistica',
		'explotacionderecursosacuaticos',
		'fisica',
		'geologiaypetroleo',
		'idiomasextranjerosconpropositosespecificos',
		'ingenieriacivil',
		'matematica',
		'politicaeducacional',
		'psicologia',
		'quimica',
		'zoologia'
	);

	// Recorrer el arreglo de perfiles funcionales para buscar uno de los perfiles válidos
	$perfil = '';
	foreach ($perfil_usuario as $perfil_item) {
		if (in_array(strtolower($perfil_item), $perfiles_validos)) {
			$perfil = $perfil_item;
			break; // Se toma el primero que coincida
		}
	}

	if ($perfil === '') {
		throw new Exception("El perfil funcional no está definido.");
	}

	// Escapar el valor para evitar inyección SQL
	$perfilEscaped = pg_escape_string($perfil);
	$perfilLiteral = "'" . $perfilEscaped . "'";

	/*
		Se utiliza translate() para eliminar acentos y lower() para evitar
		diferencias de mayúsculas/minúsculas en la comparación del perfil con
		el campo depto_principal de la tabla materias.
	*/
	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas as t_p
			JOIN
				materias as t_m ON t_p.id_materia_prog = t_m.id_materia
			WHERE
				replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
				ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
				AND t_p.estado = 'sac'
			ORDER BY
				nombre_materia";

	return toba::db('catedras')->consultar($sql);
}

	
//// para SAC

	
		///// listado filtrado para sac para firmar
function get_listado_filtrado_sac($usuario_id, $deptos_principales)
{
	// Si la lista está vacía, podrías optar por no filtrar o devolver un conjunto vacío.
	if (empty($deptos_principales)) {
		// Aquí podrías devolver todos los registros en estado 'sac' o ninguno.
		// return array(); // O ejecutar la consulta sin filtro.
		// Por ejemplo, ejecutar la consulta sin filtro:
		$filtro_deptos = "1=1";
	} else {
		// Crear la lista de literales SQL. Se asume que la función quote() se encarga de poner comillas simples
		$lista = array();
		foreach ($deptos_principales as $dept) {
			// Puedes usar quote($dept) o construir el literal manualmente:
			$lista[] = quote($dept);
			// Alternativamente, si no tienes quote():
			// $lista[] = "'" . pg_escape_string($dept) . "'";
		}
		// Convertir el array a una lista separada por comas
		$lista_str = implode(',', $lista);
		$filtro_deptos = "t_m.depto_principal IN ($lista_str)";
	}

	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas as t_p
			JOIN
				materias as t_m ON t_p.id_materia_prog = t_m.id_materia
			WHERE
				t_p.estado = 'sac'
				AND $filtro_deptos
			ORDER BY nombre_materia";
	return toba::db('catedras')->consultar($sql);
}

	
///// listado filtrado para sac para firmar
function get_listado_filtrado_control_sac($usuario_id, $deptos_principales)
{
	// Si la lista está vacía, podrías optar por no filtrar o devolver un conjunto vacío.
	if (empty($deptos_principales)) {
		// Aquí podrías devolver todos los registros en estado 'sac' o ninguno.
		// return array(); // O ejecutar la consulta sin filtro.
		// Por ejemplo, ejecutar la consulta sin filtro:
		$filtro_deptos = "1=1";
	} else {
		// Crear la lista de literales SQL. Se asume que la función quote() se encarga de poner comillas simples
		$lista = array();
		foreach ($deptos_principales as $dept) {
			// Puedes usar quote($dept) o construir el literal manualmente:
			$lista[] = quote($dept);
			// Alternativamente, si no tienes quote():
			// $lista[] = "'" . pg_escape_string($dept) . "'";
		}
		// Convertir el array a una lista separada por comas
		$lista_str = implode(',', $lista);
		$filtro_deptos = "t_m.depto_principal IN ($lista_str)";
	}

	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas as t_p
			JOIN
				materias as t_m ON t_p.id_materia_prog = t_m.id_materia
			WHERE
				t_p.estado IN = ('docente', 'depto')
				AND $filtro_deptos
			ORDER BY nombre_materia";
	return toba::db('catedras')->consultar($sql);
}

		
	
	
	


	
		//---- listado para sac aprobados
function get_listado_enviados_sac($usuario_id, $deptos_principales)
{
	// --- Preparar el filtro por departamentos principales ---
	if (empty($deptos_principales)) {
		// Si la lista está vacía, no se aplica filtro alguno (1=1 siempre es verdadero)
		$filtro_deptos = "1=1";
	} else {
		// Se arma la lista de departamentos con la función quote() para evitar inyecciones SQL
		$lista = array();
		foreach ($deptos_principales as $dept) {
			$lista[] = quote($dept);
		}
		$lista_str = implode(',', $lista);
		$filtro_deptos = "t_m.depto_principal IN ($lista_str)";
	}

	// --- Consulta SQL con el filtro aplicado ---
	$sql = "SELECT
				t_p.*,
				t_m.*
			FROM
				programas AS t_p
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
			WHERE
				t_p.estado IN ('aprobado')
				AND $filtro_deptos
			ORDER BY nombre_materia";

	return toba::db('catedras')->consultar($sql);
}


	
	
	
	
	
	
}
?>