<?php
class dt_planificaciones extends catedras_datos_tabla
{

	
//----------------
		function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_planificacion'])) {
			$where[] = " id_planificacion = ".quote($filtro['id_planificacion']);
		}
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = " ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = " nombre_materia ILIKE ".quote('%'.$filtro['nombre_materia'].'%');
		}
		if (isset($filtro['estado_planificacion'])) {
			$where[] = " estado_planificacion = ".quote($filtro['estado_planificacion']);
		}
		if (isset($filtro['apellido_resp'])) {
//            $where[] = " apellido_resp LIKE ".quote($filtro['apellido_resp']);
			$where[] = " apellido_resp ILIKE ".quote('%'.$filtro['apellido_resp'].'%');
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado ILIKE ".quote("%{$filtro['periodo_dictado']}%");
		}        
		
		$sql ="SELECT
				t_pl.*,
				t_m.*,
				t_p.*
			FROM
				planificaciones AS t_pl
			JOIN
				programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa    
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia    
			ORDER BY
				dni_resp";        if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		
		//toba::notificacion()->info("El valor de la variable es: " . $sql);
		return toba::db('catedras')->consultar($sql);
	}

	function get_listado_de_repuesto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_planificacion'])) {
			$where[] = " id_planificacion = ".quote($filtro['id_planificacion']);
		}
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = " ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = " nombre_materia ILIKE ".quote('%'.$filtro['nombre_materia'].'%');
		}
		if (isset($filtro['estado_planificacion'])) {
			$where[] = " estado_planificacion = ".quote($filtro['estado_planificacion']);
		}
		if (isset($filtro['apellido_resp'])) {
//            $where[] = " apellido_resp LIKE ".quote($filtro['apellido_resp']);
			$where[] = " apellido_resp ILIKE ".quote('%'.$filtro['apellido_resp'].'%');
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado ILIKE ".quote("%{$filtro['periodo_dictado']}%");
		}        
		
		$sql ="SELECT
				t_pl.*,
				t_m.*,
				t_p.*
			FROM
				planificaciones AS t_pl
			JOIN
				programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa    
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia    
			ORDER BY
				dni_resp";        if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		
		//toba::notificacion()->info("El valor de la variable es: " . $sql);
		return toba::db('catedras')->consultar($sql);
	}

	
//----------------------    
	
	function get_datos_planificacion($id_planificacion_seleccionada)
{
	$sql = "
		SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.id_planificacion = " . quote($id_planificacion_seleccionada);
	
	return toba::db('catedras')->consultar_fila($sql);
}
//-----------------------------
	
	function get_listado_magic($filtro=array())
	{
		$where = array();
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = "ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		$sql = "SELECT
			t_p.id_planificacion,
			t_p.ano_acad_planif,
			t_p.id_prog_planif,
			t_p.equipo_catedra_planif,
			t_p.dist_horaria_planif,
			t_p.horarios_consulta,
			t_p.otras_tareas,
			t_p.bibliografia_pedida,
			t_p.actividades_internas,
			t_p.actividades_externas,
			t_p.libros_pub,
			t_p.apuntes_pub,
			t_p.guia_trabajos_pub,
			t_p.publicaciones_periodicas,
			t_p.opinion_area,
			t_p.opinion_depto,
			t_p.observaciones_planif,
			t_p.comentarios_planif
		FROM
			planificaciones as t_p
		ORDER BY equipo_catedra_planif";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " 
			AND t_pl.estado_planificacion = 'docente'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
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
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
			ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
		AND t_pl.estado_planificacion = 'depto'
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado_sac($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'sac'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//---------------- este creo que ya no se usa
	function get_listado_filtrado_aprobado($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'aprobado'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----- SELECTS para SAC 
	
	function get_listado_sac_filtrado_docente($usuario_id, $perfil_usuario)
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
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion = 'docente'
			AND $filtro_deptos
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
//-----    
	function get_listado_sac_filtrado_depto($usuario_id, $perfil_usuario)
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
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion = 'depto'
			AND $filtro_deptos
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}

//------
	function get_listado_sac_filtrado_aprobado($usuario_id, $perfil_usuario)
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
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion = 'aprobado'
			AND $filtro_deptos
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}

	
//----------------
	function get_listado_filtrado_docente_depto($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion IN ('docente','depto')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
//----------------
	
	function get_listado_estado_depto_aprobado()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('depto','aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	
	function get_listado_estado_docente()
	{
			$sql = 
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('docente')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	
	function get_listado_estado_docente_depto()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('docente','depto')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	//--------------    

	function get_listado_estado_depto()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('depto')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		}
		
//------------
			function get_listado_estado_aprobado()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('aprobado')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		}  
	
//----------------
	
	function get_listado_enviados($usuario_id)
	{
		$sql = 
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia      
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_pl.estado_planificacion IN ('depto', 'aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//--------------------------    
	
	function get_listado_enviados_depto($usuario_id, $perfil_usuario)
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
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
			ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
		AND t_pl.estado_planificacion = 'aprobado'
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	
	function get_listado_enviados_depto_falla($usuario_id)
	{
		$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia      
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_pl.estado_planificacion IN ('aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    

	
	
}
?>