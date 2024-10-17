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
	
	
	
	//---- Nuevo método para obtener datos filtrados por estado ----
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

	
		
	
	
}
?>