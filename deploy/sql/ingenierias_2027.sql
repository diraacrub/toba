-- Desde 2027 los programas de las ingenierias (ICIB, IELB, IETB, IMEB, IPEB,
-- IQUB) se editan en la operacion Programas y Planificaciones, como los de las
-- demas carreras, con campos propios (php/ingenierias/ingenierias.php). Hasta
-- 2026 solo se cargaban para asociarlos a planificaciones e informes.
--
-- materias: datos del plan de estudios de las ingenierias. En la operacion se
-- muestran sin editar; se cargan desde deptos (POST /rest/materias-alta) o en
-- el ABM de Materias.
--
-- programas: lo que escribe el docente. i_competencias_que_aporta es un JSON
-- con los codigos de las competencias de la carrera que marco (las del libro
-- rojo de CONFEDI, en el codigo).
--
-- Se corre una vez por base, ANTES de desplegar el codigo:
--   psql ... -d toba_3_4 -f deploy/sql/ingenierias_2027.sql
ALTER TABLE huayca.materias ADD COLUMN IF NOT EXISTS i_bloque_curricular text;
ALTER TABLE huayca.materias ADD COLUMN IF NOT EXISTS i_correlativas_anteriores text;
ALTER TABLE huayca.materias ADD COLUMN IF NOT EXISTS i_correlativas_posteriores text;
ALTER TABLE huayca.materias ADD COLUMN IF NOT EXISTS i_reconocimiento_t_f numeric;

ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS i_propositos text;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS i_competencias_espacio_curricular text;
ALTER TABLE huayca.programas ADD COLUMN IF NOT EXISTS i_competencias_que_aporta text;

COMMENT ON COLUMN huayca.materias.i_bloque_curricular IS 'Ingenierias: bloque curricular (desde 2027)';
COMMENT ON COLUMN huayca.materias.i_correlativas_anteriores IS 'Ingenierias: correlativas anteriores (desde 2027)';
COMMENT ON COLUMN huayca.materias.i_correlativas_posteriores IS 'Ingenierias: correlativas posteriores (desde 2027)';
COMMENT ON COLUMN huayca.materias.i_reconocimiento_t_f IS 'Ingenierias: reconocimiento T/F (desde 2027)';
COMMENT ON COLUMN huayca.programas.i_propositos IS 'Ingenierias: propositos (desde 2027)';
COMMENT ON COLUMN huayca.programas.i_competencias_espacio_curricular IS 'Ingenierias: competencias del espacio curricular (desde 2027)';
COMMENT ON COLUMN huayca.programas.i_competencias_que_aporta IS 'Ingenierias: JSON con los codigos de las competencias de la carrera que aporta (desde 2027)';
