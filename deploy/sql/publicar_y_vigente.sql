-- Desde 2027: publicar los documentos, el responsable vigente de cada programa
-- y la foto del equipo de cada informe enviado.
--
-- 1. Publicado (programas e informes). Lo que crea el informe de Mocovi desde
--    deptos nace sin publicar: el docente no lo ve hasta que Secretaria lo
--    publica desde deptos (POST /rest/publicar). La planificacion va con su
--    programa (en la operacion Programas y Planificaciones son uno). Lo que ya
--    existe queda publicado (DEFAULT true), y tambien lo que se carga a mano en
--    los ABM.
--
-- 2. El responsable vigente del programa (*_resp_vig): el que Mocovi dice hoy,
--    aunque el programa ya este firmado con otro. NULL = el del programa. Lo
--    escribe POST /rest/mocovi-anio (php/administracion/mocovi_anio.php). Lo
--    usa deptos (Mis catedras, salidas de campo) por /rest/asignaciones y
--    /rest/documentos.
--
-- 3. informes.equipo_inf: el equipo vigente cuando el docente envio el
--    informe (JSON [apellido, nombre, legajo, cargo, dedicacion], como
--    programas.equipo_catedra). Mientras el informe esta en estado docente, el
--    encabezado muestra el equipo vigente de huayca.materias_equipo.
--
-- Se corre una vez por base, ANTES de desplegar el codigo:
--   psql ... -d toba_3_4 -f deploy/sql/publicar_y_vigente.sql
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS publicado boolean NOT NULL DEFAULT true;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS publicado_cambio timestamp;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS publicado_por varchar(100);

ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS publicado boolean NOT NULL DEFAULT true;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS publicado_cambio timestamp;
ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS publicado_por varchar(100);

ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS id_designacion_vig integer;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS legajo_resp_vig integer;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS dni_resp_vig integer;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS apellido_resp_vig varchar(255);
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS nombre_resp_vig varchar(255);
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS cargo_resp_vig varchar(255);
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS resp_vig_actualizado timestamp;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS resp_vig_por varchar(100);

ALTER TABLE huayca.informes ADD COLUMN IF NOT EXISTS equipo_inf text;

COMMENT ON COLUMN huayca.programas.publicado IS 'El docente lo ve (con su planificacion); lo publica Secretaria desde deptos (desde 2026-11-18)';
COMMENT ON COLUMN huayca.informes.publicado IS 'El docente lo ve; lo publica Secretaria desde deptos (desde 2026-11-18)';
COMMENT ON COLUMN huayca.programas.legajo_resp_vig IS 'Responsable vigente segun Mocovi; NULL = el del programa (desde 2026-11-18)';
COMMENT ON COLUMN huayca.informes.equipo_inf IS 'El equipo vigente al enviar el informe (desde 2026-11-18)';
