<?php
class dt_res_optativas extends catedras_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_resolucion'])) {
			$where[] = "id_resolucion = ".quote($filtro['id_resolucion']);
		}
		if (isset($filtro['id_materia_resop'])) {
			$where[] = "id_materia_resop = ".quote($filtro['id_materia_resop']);
		}
		if (isset($filtro['tipo_norma'])) {
			$where[] = "tipo_norma ILIKE ".quote("%{$filtro['tipo_norma']}%");
		}
		if (isset($filtro['numero_norma'])) {
			$where[] = "numero_norma ILIKE ".quote("%{$filtro['numero_norma']}%");
		}
		if (isset($filtro['accion'])) {
			$where[] = "accion ILIKE ".quote("%{$filtro['accion']}%");
		}
		if (isset($filtro['estado_resop'])) {
			$where[] = "estado_resop ILIKE ".quote("%{$filtro['estado_resop']}%");
		}
		$sql = "SELECT
			t_ro.id_resolucion,
			t_ro.id_materia_resop,
			t_ro.tipo_norma,
			t_ro.numero_norma,
			t_ro.enlace,
			t_ro.accion,
			t_ro.observaciones_resop,
			t_ro.estado_resop
		FROM
			res_optativas as t_ro
		ORDER BY tipo_norma";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

}

?>