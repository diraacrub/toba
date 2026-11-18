-- El responsable propio del informe de cátedra (desde 2027).
--
-- Hasta ahora el informe era del responsable de su programa. Pero el programa
-- se envía en marzo (o agosto) y ya no se toca, y el informe se completa en
-- diciembre: si en Mocovi cambió el responsable, el informe tiene que pasar al
-- nuevo. Lo escribe POST /rest/mocovi-anio
-- (php/administracion/actualizar_catedras.php), solo si el informe está en
-- estado docente y no venció su plazo.
--
-- NULL = el del programa, como siempre: los informes de 2025 y 2026 y los que
-- no cambiaron quedan igual.
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS id_designacion_inf integer;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS legajo_resp_inf integer;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS dni_resp_inf integer;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS apellido_resp_inf varchar(255);
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS nombre_resp_inf varchar(255);
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS cargo_resp_inf varchar(255);
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS resp_inf_actualizado timestamp;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS resp_inf_por varchar(100);

-- El programa de cada informe, con el responsable del informe si tiene uno.
-- Las consultas de informes (dt_informes, /rest/documentos) la usan en lugar de
-- programas: "JOIN informes_programas AS t_p ON t_p.id_informe_programa = t_i.id_informe"
-- da las mismas columnas que "JOIN programas AS t_p ON t_i.id_prog_informe = t_p.id_programa".
--
-- Tiene todas las columnas de programas, una por una (no p.*, para poder
-- reemplazar las del responsable): si se agrega una columna a programas que
-- haga falta en los informes, hay que sumarla acá.
CREATE OR REPLACE VIEW huayca.informes_programas AS
SELECT
	i.id_informe AS id_informe_programa,
	p.id_programa,
	coalesce(i.id_designacion_inf, p.id_designacion) AS id_designacion,
	p.id_asignacion,
	coalesce(i.legajo_resp_inf, p.legajo_resp) AS legajo_resp,
	coalesce(i.dni_resp_inf, p.dni_resp) AS dni_resp,
	coalesce(i.apellido_resp_inf, p.apellido_resp) AS apellido_resp,
	coalesce(i.nombre_resp_inf, p.nombre_resp) AS nombre_resp,
	coalesce(i.cargo_resp_inf, p.cargo_resp) AS cargo_resp,
	p.equipo_catedra,
	p.id_materia_prog,
	p.periodo_dictado,
	p.ano_academico,
	p.fundamentacion,
	p.objetivos,
	p.programa_analitico,
	p.bibliografia,
	p.propuesta_metodologica,
	p.evaluacion_acreditacion,
	p.distribucion_horaria,
	p.cronograma_tentativo,
	p.estado,
	p.observaciones,
	p.comentarios,
	p.firma_doc,
	p.firma_dto,
	p.firma_sac,
	p.horas_teoricas,
	p.horas_practicas,
	p.horas_teoricopracticas
FROM huayca.informes i
JOIN huayca.programas p ON p.id_programa = i.id_prog_informe;
COMMENT ON VIEW huayca.informes_programas IS 'El programa de cada informe con el responsable del informe si tiene (desde 2026-11-18)';
