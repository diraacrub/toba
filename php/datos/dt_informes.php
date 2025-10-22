<?php
class dt_informes extends catedras_datos_tabla
{
	
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia  
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

	
	
	function get_listado_magis($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['estado_informe'])) {
			$where[] = "estado_informe ILIKE ".quote("%{$filtro['estado_informe']}%");
		}
		$sql = "SELECT
			t_i.id_informe,
			t_i.id_prog_informe,
			t_i.inscriptos,
			t_i.comenzaron,
			t_i.aprobaron,
			t_i.abandonaron,
			t_i.desaprobaron,
			t_i.causas_abandono_desap,
			t_i.caract_grupo,
			t_i.estrategias,
			t_i.consideraciones_interior,
			t_i.analisis_actividades,
			t_i.suficiencia_adecuacion,
			t_i.evaluacion_ays,
			t_i.articulacion,
			t_i.capacitacion,
			t_i.analisis_por_cargo,
			t_i.estado_informe,
			t_i.comentarios_inf,
			t_i.otro_analisis,
			t_i.firma_doc_inf,
			t_i.firma_dto_inf,
			t_i.firma_sac_inf
		FROM
			informes as t_i
		ORDER BY causas_abandono_desap";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}


	function get_listado_repuesto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia  
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

	
	function get_listado_estado_depto_aprobado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe IN ('depto','aprobado')   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}    
	
	
	function get_listado_enviados_borrar($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe IN ('depto','aprobado')   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}   

	function get_listado_estado_docente($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe = 'docente'   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}  
	

	function get_listado_estado_docente_depto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe IN ('depto','docente')   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}    
	

	function get_listado_estado_depto()
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe = 'depto'   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}  
	

	function get_listado_estado_aprobado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_informe'])) {
			$where[] = "id_informe = ".quote($filtro['id_informe']);
		}
		if (isset($filtro['id_prog_informe'])) {
			$where[] = "id_prog_informe = ".quote($filtro['id_prog_informe']);
		}
		$sql =
		"SELECT  
			t_i.*,  
			t_p.*,  
			t_m.*  
		FROM  
			informes AS t_i  
		JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa  
		JOIN materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE t_i.estado_informe = 'aprobado'   
		ORDER BY t_m.nombre_materia";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}  
		
		
	
	
function get_datos_informe($id_informe_seleccionado)
{
	$sql = "
		SELECT
			t_i.*,
			t_m.*,
			t_p.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_i.id_informe = " . quote($id_informe_seleccionado);
	
	return toba::db('catedras')->consultar_fila($sql);
}

	
	function get_listado_filtrado($usuario_id)
	{
		$sql = "SELECT
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_i.estado_informe = 'docente'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    


	
		
	function get_listado_enviados($usuario_id)
	{
		$sql = "SELECT
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_i.estado_informe IN ('depto', 'aparobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    

	
	
	
	
	
	function get_listado_filtrado_sac($usuario_id)
	{
		$sql = "SELECT
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_i.estado_informe = 'sac'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	function get_listado_filtrado_docente_depto($usuario_id)
	{
		$sql = "SELECT
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_i.estado_informe IN ('docente','depto')
			
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    
	
	
	
	function get_listado_filtrado_aprobado($usuario_id)
	{
		$sql = "SELECT
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_i.estado_informe = 'aprobado'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    
	
	
	
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
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes AS t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
			ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
		AND t_i.estado_informe = 'depto'
		ORDER BY nombre_materia";
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
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes as t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_i.estado_informe = 'docente'
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
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes as t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_i.estado_informe = 'depto'
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
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes as t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_i.estado_informe = 'aprobado'
			AND $filtro_deptos
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}

	


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
			t_i.*,
			t_p.*,
			t_m.*
		FROM
			informes as t_i
		JOIN
			programas AS t_p ON t_i.id_prog_informe = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			replace(translate(lower(t_m.depto_principal), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
			ILIKE replace(translate(lower($perfilLiteral), 'áéíóúÁÉÍÓÚ', 'aeiouaeiou'), ' ', '')
		AND t_i.estado_informe = 'aprobado'
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
	

	
}
?>