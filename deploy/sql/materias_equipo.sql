-- El equipo de cada materia por año, tal como lo trae el informe de Mocovi
-- (el que se sube en deptos /programas: una fila por materia, carrera, plan y
-- docente, con su rol). Lo escribe POST /rest/mocovi-anio
-- (php/informes/materias_equipo.php) cada vez que Secretaría sube el informe:
--   - una fila idéntica (mismas columnas de Mocovi y mismo año) se deja como está;
--   - una que ya no viene pasa a 'borrado' (con la fecha de baja);
--   - una borrada que vuelve idéntica se reactiva (conserva sus observaciones);
--   - una nueva se agrega.
-- Un cambio de categoría o de rol es una baja y un alta: queda la historia.
--
-- La lee el informe de cátedra (punto 11, análisis por cargo): el select de
-- docentes es el equipo de la materia de ese año, activos y dados de baja.
--
-- Va en el esquema de negocio (huayca), no en el de la instancia de Toba:
-- "bin/toba proyecto regenerar" no lo toca. Se corre una vez por base:
--   psql ... -d toba_3_4 -f deploy/sql/materias_equipo.sql
CREATE TABLE IF NOT EXISTS huayca.materias_equipo (
	id_materia_equipo serial PRIMARY KEY,
	ano_academico     integer NOT NULL,
	-- Las columnas del informe de Mocovi, como texto tal cual (los ceros de
	-- adelante de los códigos importan). Ordenanza es plan_mocovi.
	materia           varchar(255) NOT NULL,
	cod_guarani       varchar(50)  NOT NULL,   -- Cod SIU
	cod_carrera       varchar(50)  NOT NULL,   -- Carrera
	plan_mocovi       varchar(50)  NOT NULL,   -- Ordenanza
	docente           varchar(255) NOT NULL,   -- 'APELLIDO, NOMBRE'
	legajo            varchar(20)  NOT NULL,
	categoria         varchar(20)  NOT NULL,   -- 'JTP3'
	id_designacion    varchar(20)  NOT NULL,   -- Desig
	modulo            varchar(50)  NOT NULL,
	rol               varchar(20)  NOT NULL,   -- Resp / Aux
	periodo           varchar(50)  NOT NULL,   -- '1CUAT', '2BIMES2C', ...
	-- La materia del catálogo (cod_guarani, cod_carrera, plan_mocovi); NULL si
	-- no está. El departamento sale de ahí.
	id_materia        integer,
	estado            varchar(10)  NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'borrado')),
	observaciones     text,
	alta              timestamp NOT NULL DEFAULT now(),  -- cuando apareció en el informe
	baja              timestamp,                         -- cuando dejó de venir (si está borrado)
	actualizado       timestamp NOT NULL DEFAULT now(),
	actualizado_por   varchar(100)
);
CREATE INDEX IF NOT EXISTS materias_equipo_dictado
	ON huayca.materias_equipo (ano_academico, cod_guarani, cod_carrera, plan_mocovi);
CREATE INDEX IF NOT EXISTS materias_equipo_materia
	ON huayca.materias_equipo (id_materia, ano_academico);
COMMENT ON TABLE huayca.materias_equipo IS 'Equipo de cada materia por año, del informe de Mocovi que se sube en deptos (desde 2026-11-18)';
