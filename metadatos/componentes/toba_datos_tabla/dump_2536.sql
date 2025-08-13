------------------------------------------------------------
--[2536]--  DT - movimientos 
------------------------------------------------------------

------------------------------------------------------------
-- apex_objeto
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto (proyecto, objeto, anterior, identificador, reflexivo, clase_proyecto, clase, punto_montaje, subclase, subclase_archivo, objeto_categoria_proyecto, objeto_categoria, nombre, titulo, colapsable, descripcion, fuente_datos_proyecto, fuente_datos, solicitud_registrar, solicitud_obj_obs_tipo, solicitud_obj_observacion, parametro_a, parametro_b, parametro_c, parametro_d, parametro_e, parametro_f, usuario, creacion, posicion_botonera) VALUES (
	'catedras', --proyecto
	'2536', --objeto
	NULL, --anterior
	NULL, --identificador
	NULL, --reflexivo
	'toba', --clase_proyecto
	'toba_datos_tabla', --clase
	'13', --punto_montaje
	'dt_movimientos', --subclase
	'datos/dt_movimientos.php', --subclase_archivo
	NULL, --objeto_categoria_proyecto
	NULL, --objeto_categoria
	'DT - movimientos', --nombre
	NULL, --titulo
	NULL, --colapsable
	NULL, --descripcion
	'catedras', --fuente_datos_proyecto
	'catedras', --fuente_datos
	NULL, --solicitud_registrar
	NULL, --solicitud_obj_obs_tipo
	NULL, --solicitud_obj_observacion
	NULL, --parametro_a
	NULL, --parametro_b
	NULL, --parametro_c
	NULL, --parametro_d
	NULL, --parametro_e
	NULL, --parametro_f
	NULL, --usuario
	'2025-08-12 17:27:09', --creacion
	NULL  --posicion_botonera
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_db_registros
------------------------------------------------------------
INSERT INTO apex_objeto_db_registros (objeto_proyecto, objeto, max_registros, min_registros, punto_montaje, ap, ap_clase, ap_archivo, tabla, tabla_ext, alias, modificar_claves, fuente_datos_proyecto, fuente_datos, permite_actualizacion_automatica, esquema, esquema_ext) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	NULL, --max_registros
	NULL, --min_registros
	'13', --punto_montaje
	'1', --ap
	NULL, --ap_clase
	NULL, --ap_archivo
	'movimientos', --tabla
	NULL, --tabla_ext
	NULL, --alias
	'0', --modificar_claves
	'catedras', --fuente_datos_proyecto
	'catedras', --fuente_datos
	'1', --permite_actualizacion_automatica
	NULL, --esquema
	'public'  --esquema_ext
);

------------------------------------------------------------
-- apex_objeto_db_registros_col
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'952', --col_id
	'id_movimiento', --columna
	'E', --tipo
	'1', --pk
	'movimientos_id_movimiento_seq', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'953', --col_id
	'nombre_tabla', --columna
	'C', --tipo
	'0', --pk
	NULL, --secuencia
	'50', --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'954', --col_id
	'id_tabla', --columna
	'E', --tipo
	'0', --pk
	NULL, --secuencia
	NULL, --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'955', --col_id
	'fecha_movimiento', --columna
	'T', --tipo
	'0', --pk
	NULL, --secuencia
	NULL, --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'956', --col_id
	'tipo_movimiento', --columna
	'C', --tipo
	'0', --pk
	NULL, --secuencia
	'255', --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'957', --col_id
	'usuario_movimiento', --columna
	'C', --tipo
	'0', --pk
	NULL, --secuencia
	'100', --largo
	NULL, --no_nulo
	'1', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'958', --col_id
	'observaciones', --columna
	'X', --tipo
	'0', --pk
	NULL, --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'catedras', --objeto_proyecto
	'2536', --objeto
	'959', --col_id
	'estado_mov', --columna
	'C', --tipo
	'0', --pk
	NULL, --secuencia
	'50', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'movimientos'  --tabla
);
--- FIN Grupo de desarrollo 0
