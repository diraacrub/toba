<?php
class dt_programas extends catedras_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_p.id_programa,
			t_p.id_designacion,
			t_p.id_asignacion,
			t_p.legajo_resp,
			t_p.dni_resp,
			t_p.apellido_resp,
			t_p.nombre_resp,
			t_p.cargo_resp,
			t_p.equipo_catedra,
			t_p.id_materia_prog,
			t_p.periodo_dictado,
			t_p.ano_academico,
			t_p.fundamentacion,
			t_p.objetivos,
			t_p.programa_analitico,
			t_p.bibliografia,
			t_p.propuesta_metodologica,
			t_p.evaluacion_acreditacion,
			t_p.distribucion_horaria,
			t_p.cronograma_tentativo,
			t_p.estado,
			t_p.observaciones,
			t_p.comentarios,
			t_p.firma_doc,
			t_p.firma_dto,
			t_p.firma_sac,
			t_p.horas_teoricas,
			t_p.horas_practicas,
			t_p.horas_teoricopracticas
		FROM
			programas as t_p
		ORDER BY apellido_resp";
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