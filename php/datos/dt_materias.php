<?php
class dt_materias extends catedras_datos_tabla
{
	
	
///////
	function get_descripciones()
	{
		$sql = "SELECT
			t_p.*,
			t_m.*
		FROM
			programas as t_p,
			materias as t_m
		WHERE
				t_p.id_materia_prog = t_m.id_materia";
		return toba::db('catedras')->consultar($sql);
	}



	function get_listado()
	{
		$sql = "SELECT
			t_m.id_materia,
			t_m.nombre_carrera,
			t_m.nombre_materia,
			t_m.ano_plan,
			t_m.periodo_plan,
			t_m.horas_totales,
			t_m.horas_semanales,
			t_m.depto_principal,
			t_m.depto,
			t_m.area,
			t_m.orientacion,
			t_m.contenidos_minimos,
			t_m.correlativas_para_cursar,
			t_m.correlativas_para_aprobar,
			t_m.competencias,
			t_m.optativa,
			t_m.trayecto,
			t_m.cod_carrera,
			t_m.plan_guarani,
			t_m.version_guarani,
			t_m.plan_mocovi,
			t_m.plan_ordenanzas,
			t_m.cod_guarani,
			t_m.observaciones
		FROM
			materias as t_m
		ORDER BY nombre_carrera";
		return toba::db('catedras')->consultar($sql);
	}

	
	
	
	
	
	
}
?>