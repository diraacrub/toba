<?php
class dt_oferta_optativas extends catedras_datos_tabla
{
	
		function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_optativa'])) {
			$where[] = "id_optativa = ".quote($filtro['id_optativa']);
		}
		if (isset($filtro['id_materia_opt'])) {
			$where[] = "id_materia_opt = ".quote($filtro['id_materia_opt']);
		}
		if (isset($filtro['ano_acad_opt'])) {
			$where[] = "ano_acad_opt = ".quote($filtro['ano_acad_opt']);
		}
		if (isset($filtro['periodo_opt'])) {
			$where[] = "periodo_opt ILIKE ".quote("%{$filtro['periodo_opt']}%");
		}
		if (isset($filtro['estado_opt'])) {
			$where[] = "estado_opt ILIKE ".quote("%{$filtro['estado_opt']}%");
		}
		
		$sql = "SELECT
			t_oo.*,
			t_m.*
		FROM
			oferta_optativas AS t_oo
		JOIN
			materias AS t_m
		ON
			t_m.id_materia = t_oo.id_materia_opt
		ORDER BY t_oo.periodo_opt";
		
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

		function get_listado_repuesto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_optativa'])) {
			$where[] = "id_optativa = ".quote($filtro['id_optativa']);
		}
		if (isset($filtro['id_materia_opt'])) {
			$where[] = "id_materia_opt = ".quote($filtro['id_materia_opt']);
		}
		if (isset($filtro['ano_acad_opt'])) {
			$where[] = "ano_acad_opt = ".quote($filtro['ano_acad_opt']);
		}
		if (isset($filtro['periodo_opt'])) {
			$where[] = "periodo_opt ILIKE ".quote("%{$filtro['periodo_opt']}%");
		}
		if (isset($filtro['estado_opt'])) {
			$where[] = "estado_opt ILIKE ".quote("%{$filtro['estado_opt']}%");
		}
		
		$sql = "SELECT
			t_oo.*,
			t_m.*
		FROM
			oferta_optativas AS t_oo
		JOIN
			materias AS t_m
		ON
			t_m.id_materia = t_oo.id_materia_opt
		ORDER BY t_oo.periodo_opt";
		
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}
	
	function get_listado_original($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_optativa'])) {
			$where[] = "id_optativa = ".quote($filtro['id_optativa']);
		}
		if (isset($filtro['id_materia'])) {
			$where[] = "id_materia = ".quote($filtro['id_materia']);
		}
		if (isset($filtro['ano_acad_opt'])) {
			$where[] = "ano_acad_opt = ".quote($filtro['ano_acad_opt']);
		}
		if (isset($filtro['periodo'])) {
			$where[] = "periodo ILIKE ".quote("%{$filtro['periodo']}%");
		}
		if (isset($filtro['estado'])) {
			$where[] = "estado ILIKE ".quote("%{$filtro['estado']}%");
		}
		$sql = "SELECT
			t_oo.id_optativa,
			t_oo.id_materia,
			t_oo.ano_acad_opt,
			t_oo.periodo,
			t_oo.estado,
			t_oo.observaciones
		FROM
			oferta_optativas as t_oo
		ORDER BY periodo";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

}
?>