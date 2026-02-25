<?php
class dt_personas extends catedras_datos_tabla
{
	
	function get_listado($filtro=array())
{
	$where = array();
	if (isset($filtro['id_persona'])) {
		$where[] = "id_persona = ".quote($filtro['id_persona']);
	}
	if (isset($filtro['apellido'])) {
		$where[] = "apellido ILIKE ".quote("%{$filtro['apellido']}%");
	}
	if (isset($filtro['nombre'])) {
		$where[] = "nombre ILIKE ".quote("%{$filtro['nombre']}%");
	}
	if (isset($filtro['fecha_nac'])) {
		$where[] = "fecha_nac = ".quote($filtro['fecha_nac']);
	}
	if (isset($filtro['hijos'])) {
		$where[] = "hijos ILIKE ".quote("%{$filtro['hijos']}%");
	}

	$sql = "SELECT
		t_p.id_persona,
		t_p.apellido,
		t_p.nombre,
		t_p.fecha_nac,
		t_p.hijos
	FROM
		personas as t_p
	ORDER BY nombre";

	if (count($where)>0) {
		$sql = sql_concatenar_where($sql, $where);
	}

	$datos = toba::db('catedras')->consultar($sql);

	// &#128313; transformar JSON &#8594; cantidad de hijos
	
	foreach ($datos as $i => $fila) {
	if (!empty($fila['hijos'])) 
	{
			$hijos = json_decode($fila['hijos'], true);
			$cant = is_array($hijos) ? count($hijos) : 0;
			$datos[$i]['hijos'] = $cant > 0 ? $cant : '-';
		} else {
			$datos[$i]['hijos'] = '-';
		}
	}
		
	
	return $datos;
}

	
	
	
	
	
	function get_listado_original($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_persona'])) {
			$where[] = "id_persona = ".quote($filtro['id_persona']);
		}
		if (isset($filtro['apellido'])) {
			$where[] = "apellido ILIKE ".quote("%{$filtro['apellido']}%");
		}
		if (isset($filtro['nombre'])) {
			$where[] = "nombre ILIKE ".quote("%{$filtro['nombre']}%");
		}
		if (isset($filtro['fecha_nac'])) {
			$where[] = "fecha_nac = ".quote($filtro['fecha_nac']);
		}
		if (isset($filtro['hijos'])) {
			$where[] = "hijos ILIKE ".quote("%{$filtro['hijos']}%");
		}
		$sql = "SELECT
			t_p.id_persona,
			t_p.apellido,
			t_p.nombre,
			t_p.fecha_nac,
			t_p.hijos
		FROM
			personas as t_p
		ORDER BY nombre";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}

}
?>