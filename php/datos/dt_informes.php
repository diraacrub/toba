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







	
}
?>