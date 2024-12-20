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
			function get_listado_filtrado_depto($usuario_id, $nombre_usuario)
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_m.depto_principal = " . quote($nombre_usuario) . " AND  t_p.estado = 'depto'
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	
		//---- listado para depto firmados
	function get_listado_enviados_depto($usuario_id, $nombre_usuario)
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_m.depto_principal = " . quote($nombre_usuario) . " AND t_p.estado IN( 'sac', 'aprobado')
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
//// para SAC

	
		///// listado filtrado para sac para firmar
			function get_listado_filtrado_sac($usuario_id, $nombre_usuario)
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
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	
		//---- listado para sac aprobados
	function get_listado_enviados_sac($usuario_id, $nombre_usuario)
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p
		JOIN
			materias as t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.estado IN( 'aprobado')
		ORDER BY nombre_materia";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	
	
	
}
?>