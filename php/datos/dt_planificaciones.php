<?php
class dt_planificaciones extends catedras_datos_tabla
{

//--------------
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_planificacion'])) {
			$where[] = " id_planificacion = ".quote($filtro['id_planificacion']);
		}
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = " ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = " nombre_materia ILIKE ".quote('%'.$filtro['nombre_materia'].'%');
		}
		if (isset($filtro['estado_planificacion'])) {
			$where[] = " estado_planificacion = ".quote($filtro['estado_planificacion']);
		}
		if (isset($filtro['apellido_resp'])) {
//            $where[] = " apellido_resp LIKE ".quote($filtro['apellido_resp']);
			$where[] = " apellido_resp ILIKE ".quote('%'.$filtro['apellido_resp'].'%');

		}
		
		$sql ="SELECT
				t_pl.*,
				t_m.*,
				t_p.*
			FROM
				planificaciones AS t_pl
			JOIN
				programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa    
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia    
			ORDER BY
				dni_resp";        if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		
		//toba::notificacion()->info("El valor de la variable es: " . $sql);
		return toba::db('catedras')->consultar($sql);
	}

	
	
//----------------
		function get_listado_repuesto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_planificacion'])) {
			$where[] = " id_planificacion = ".quote($filtro['id_planificacion']);
		}
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = " ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		if (isset($filtro['nombre_materia'])) {
			$where[] = " nombre_materia ILIKE ".quote('%'.$filtro['nombre_materia'].'%');
		}
		if (isset($filtro['estado_planificacion'])) {
			$where[] = " estado_planificacion = ".quote($filtro['estado_planificacion']);
		}
		if (isset($filtro['apellido_resp'])) {
//            $where[] = " apellido_resp LIKE ".quote($filtro['apellido_resp']);
			$where[] = " apellido_resp ILIKE ".quote('%'.$filtro['apellido_resp'].'%');

		}
		
		$sql ="SELECT
				t_pl.*,
				t_m.*,
				t_p.*
			FROM
				planificaciones AS t_pl
			JOIN
				programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa    
			JOIN
				materias AS t_m ON t_p.id_materia_prog = t_m.id_materia    
			ORDER BY
				dni_resp";        if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		
		//toba::notificacion()->info("El valor de la variable es: " . $sql);
		return toba::db('catedras')->consultar($sql);
	}

//----------------------    
	
	function get_datos_planificacion($id_planificacion_seleccionada)
{
	$sql = "
		SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.id_planificacion = " . quote($id_planificacion_seleccionada);
	
	return toba::db('catedras')->consultar_fila($sql);
}
//-----------------------------
	
	function get_listado_magic($filtro=array())
	{
		$where = array();
		if (isset($filtro['ano_acad_planif'])) {
			$where[] = "ano_acad_planif = ".quote($filtro['ano_acad_planif']);
		}
		$sql = "SELECT
			t_p.id_planificacion,
			t_p.ano_acad_planif,
			t_p.id_prog_planif,
			t_p.equipo_catedra_planif,
			t_p.dist_horaria_planif,
			t_p.horarios_consulta,
			t_p.otras_tareas,
			t_p.bibliografia_pedida,
			t_p.actividades_internas,
			t_p.actividades_externas,
			t_p.libros_pub,
			t_p.apuntes_pub,
			t_p.guia_trabajos_pub,
			t_p.publicaciones_periodicas,
			t_p.opinion_area,
			t_p.opinion_depto,
			t_p.observaciones_planif,
			t_p.comentarios_planif
		FROM
			planificaciones as t_p
		ORDER BY equipo_catedra_planif";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'docente'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado_depto($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'depto'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado_sac($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'sac'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	function get_listado_filtrado_aprobado($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion = 'aprobado'
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}

//----------------
	function get_listado_filtrado_docente_depto($usuario_id)
	{
		$sql = "SELECT
			t_pl.*,
			t_p.*,
			t_m.*
		FROM
			planificaciones as t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . " AND t_pl.estado_planificacion IN ('docente','depto')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
//----------------
	
	function get_listado_estado_depto_aprobado()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('depto','aprobado')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	
	function get_listado_estado_docente()
	{
			$sql = 
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('docente')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//----------------
	
	function get_listado_estado_docente_depto()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('docente','depto')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	//--------------    

	function get_listado_estado_depto()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('depto')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		}
		
//------------
			function get_listado_estado_aprobado()
	{
			$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia
		WHERE
			t_pl.estado_planificacion IN ('aprobado')    
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
		}  
	
//----------------
	
	function get_listado_enviados($usuario_id)
	{
		$sql = 
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia      
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_pl.estado_planificacion IN ('depto', 'aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}
	
//--------------------------    
	function get_listado_enviados_depto($usuario_id)
	{
		$sql =
		"SELECT
			t_pl.*,
			t_m.*,
			t_p.*
		FROM
			planificaciones AS t_pl
		JOIN
			programas AS t_p ON t_pl.id_prog_planif = t_p.id_programa
		JOIN
			materias AS t_m ON t_p.id_materia_prog = t_m.id_materia      
		WHERE
			t_p.legajo_resp = " . quote($usuario_id) . "
			AND t_pl.estado_planificacion IN ('aprobado')
		ORDER BY legajo_resp";
		return toba::db('catedras')->consultar($sql);
	}    

	
	
}
?>