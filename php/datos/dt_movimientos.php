<?php
class dt_movimientos extends catedras_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_movimiento'])) {
			$where[] = "id_movimiento = ".quote($filtro['id_movimiento']);
		}
		if (isset($filtro['nombre_tabla'])) {
			$where[] = "nombre_tabla ILIKE ".quote("%{$filtro['nombre_tabla']}%");
		}
		if (isset($filtro['id_tabla'])) {
			$where[] = "id_tabla = ".quote($filtro['id_tabla']);
		}
		
if (isset($filtro['fecha_movimiento'])) {
	// convierto la fecha de dd/mm/yyyy a yyyy-mm-dd
	$fecha = DateTime::createFromFormat('d/m/Y', $filtro['fecha_movimiento']);
	if ($fecha !== false) {
		$where[] = "mo.fecha_movimiento::date = " . quote($fecha->format('Y-m-d'));
	}
}

		if (isset($filtro['tipo_movimiento'])) {
			$where[] = "tipo_movimiento ILIKE ".quote("%{$filtro['tipo_movimiento']}%");
		}
		if (isset($filtro['usuario_movimiento'])) {
			$where[] = "usuario_movimiento ILIKE ".quote("%{$filtro['usuario_movimiento']}%");
		}
		if (isset($filtro['observaciones'])) {
			$where[] = "mo.observaciones ILIKE ".quote("%{$filtro['observaciones']}%");
		}
		if (isset($filtro['estado_mov'])) {
			$where[] = "estado_mov ILIKE ".quote("%{$filtro['estado_mov']}%");
		}
		
		$sql = "
	SELECT
		u.nombre AS nombre_usuario,   -- &#128072; nombre del usuario toba

		-- movimientos
		mo.id_movimiento,
		mo.nombre_tabla,
		mo.id_tabla,
		mo.fecha_movimiento,
		mo.tipo_movimiento,
		mo.usuario_movimiento,
		mo.observaciones AS observaciones_mov,
		mo.estado_mov,      

		-- siempre: info de la materia
		m.nombre_materia,
		m.cod_carrera,

		-- cuando corresponda: info del programa
		prog.ano_academico,
		prog.nombre_resp,
		prog.*,        
		prog.periodo_dictado

	FROM movimientos mo

	-- usuario del movimiento
	LEFT JOIN desarrollo.apex_usuario u
		ON u.usuario = mo.usuario_movimiento

	-- si es planificación, la buscamos (si no, queda NULL)
	LEFT JOIN planificaciones pl
		ON mo.nombre_tabla = 'planificaciones'
		AND pl.id_planificacion = mo.id_tabla

	-- si es informe, la buscamos (si no, queda NULL)
	LEFT JOIN informes i
		ON mo.nombre_tabla = 'informes'
		AND i.id_informe = mo.id_tabla            
			
	-- programa “efectivo”: el propio si es 'programas', o el de la planificación si es 'planificaciones'
	LEFT JOIN programas prog
		ON prog.id_programa = CASE
			WHEN mo.nombre_tabla = 'programas'       THEN mo.id_tabla
			WHEN mo.nombre_tabla = 'planificaciones' THEN pl.id_prog_planif
			WHEN mo.nombre_tabla = 'informes'        THEN i.id_prog_informe
		END

	-- materia: directa si el movimiento es de 'materias', o la de 'prog' en los otros casos
	LEFT JOIN materias m
		ON m.id_materia = COALESCE(
			CASE WHEN mo.nombre_tabla = 'materias' THEN mo.id_tabla END,
			prog.id_materia_prog
		)

	ORDER BY mo.id_movimiento DESC
";

		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}
	
	
	
	
// get listado de repuesto 23 de sept de 2025
	
	function get_listado_repuesto($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_movimiento'])) {
			$where[] = "id_movimiento = ".quote($filtro['id_movimiento']);
		}
		if (isset($filtro['nombre_tabla'])) {
			$where[] = "nombre_tabla ILIKE ".quote("%{$filtro['nombre_tabla']}%");
		}
		if (isset($filtro['id_tabla'])) {
			$where[] = "id_tabla = ".quote($filtro['id_tabla']);
		}
		
if (isset($filtro['fecha_movimiento'])) {
	// convierto la fecha de dd/mm/yyyy a yyyy-mm-dd
	$fecha = DateTime::createFromFormat('d/m/Y', $filtro['fecha_movimiento']);
	if ($fecha !== false) {
		$where[] = "mo.fecha_movimiento::date = " . quote($fecha->format('Y-m-d'));
	}
}

		if (isset($filtro['tipo_movimiento'])) {
			$where[] = "tipo_movimiento ILIKE ".quote("%{$filtro['tipo_movimiento']}%");
		}
		if (isset($filtro['usuario_movimiento'])) {
			$where[] = "usuario_movimiento ILIKE ".quote("%{$filtro['usuario_movimiento']}%");
		}
		if (isset($filtro['observaciones'])) {
			$where[] = "mo.observaciones ILIKE ".quote("%{$filtro['observaciones']}%");
		}
		if (isset($filtro['estado_mov'])) {
			$where[] = "estado_mov ILIKE ".quote("%{$filtro['estado_mov']}%");
		}
		
		$sql = "
	SELECT
		u.nombre AS nombre_usuario,   -- &#128072; nombre del usuario toba

		-- movimientos
		mo.id_movimiento,
		mo.nombre_tabla,
		mo.id_tabla,
		mo.fecha_movimiento,
		mo.tipo_movimiento,
		mo.usuario_movimiento,
		mo.observaciones AS observaciones_mov,
		mo.estado_mov,      

		-- siempre: info de la materia
		m.nombre_materia,
		m.cod_carrera,

		-- cuando corresponda: info del programa
		prog.ano_academico,
		prog.nombre_resp,
		prog.*,        
		prog.periodo_dictado

	FROM movimientos mo

	-- usuario del movimiento
	LEFT JOIN desarrollo.apex_usuario u
		ON u.usuario = mo.usuario_movimiento

	-- si es planificación, la buscamos (si no, queda NULL)
	LEFT JOIN planificaciones pl
		ON mo.nombre_tabla = 'planificaciones'
		AND pl.id_planificacion = mo.id_tabla

	-- si es informe, la buscamos (si no, queda NULL)
	LEFT JOIN informes i
		ON mo.nombre_tabla = 'informes'
		AND i.id_informe = mo.id_tabla            
			
	-- programa “efectivo”: el propio si es 'programas', o el de la planificación si es 'planificaciones'
	LEFT JOIN programas prog
		ON prog.id_programa = CASE
			WHEN mo.nombre_tabla = 'programas'       THEN mo.id_tabla
			WHEN mo.nombre_tabla = 'planificaciones' THEN pl.id_prog_planif
			WHEN mo.nombre_tabla = 'informes'        THEN i.id_prog_informe
		END

	-- materia: directa si el movimiento es de 'materias', o la de 'prog' en los otros casos
	LEFT JOIN materias m
		ON m.id_materia = COALESCE(
			CASE WHEN mo.nombre_tabla = 'materias' THEN mo.id_tabla END,
			prog.id_materia_prog
		)

	ORDER BY mo.id_movimiento DESC
";

		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('catedras')->consultar($sql);
	}
	
	




	
	
	
	

}
?>