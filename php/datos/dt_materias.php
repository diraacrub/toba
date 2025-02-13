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



	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_materia'])) {
			$where[] = "id_materia = ".quote($filtro['id_materia']);
		}
		if (isset($filtro['nombre_carrera'])) {
			$where[] = "nombre_carrera ILIKE ".quote("%{$filtro['nombre_carrera']}%");
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = "nombre_materia ILIKE ".quote("%{$filtro['nombre_materia']}%");
		}
		if (isset($filtro['ano_plan'])) {
			$where[] = "ano_plan = ".quote($filtro['ano_plan']);
		}
		if (isset($filtro['depto_principal'])) {
			$where[] = "depto_principal ILIKE ".quote("%{$filtro['depto_principal']}%");
		}
		if (isset($filtro['cod_carrera'])) {
			$where[] = "cod_carrera ILIKE ".quote("%{$filtro['cod_carrera']}%");
		}
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
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}


	
	
	
	
	
	
}
?>