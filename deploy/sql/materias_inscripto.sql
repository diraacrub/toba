-- Los inscriptos de cada materia por año, para las salidas de campo: qué DNI
-- cursa qué materia en qué carrera. Vienen de los reportes que Secretaría pide a
-- la Dirección de Alumnos (dos por año: el envío 1, materias del 1.er
-- cuatrimestre y anuales; el 2, del 2.º) y los manda deptos (POST
-- /rest/inscriptos): cada vez, los de ese año y envío se reemplazan enteros
-- (queda un renglón en huayca.movimientos). Lo lee GET /rest/inscriptos.
--
-- Solo el DNI: los datos de las personas (nombre, CUIL, fecha de nacimiento)
-- no vienen acá.
--
-- La materia del catálogo (id_materia) es la de huayca.materias con la misma
-- combinación exacta de cod_carrera, cod_guarani, plan_guarani y
-- version_guarani; si no hay ninguna, queda NULL (el inscripto se guarda igual).
--
-- Va en el esquema de negocio (huayca), no en el de la instancia de Toba:
-- "bin/toba proyecto regenerar" no lo toca. Se corre una vez por base:
--   psql ... -d toba_3_4 -f deploy/sql/materias_inscripto.sql
CREATE TABLE IF NOT EXISTS huayca.materias_inscripto (
	id_materia_inscripto serial PRIMARY KEY,
	ano_academico   integer NOT NULL,
	envio           smallint NOT NULL CHECK (envio IN (1, 2)),
	cod_carrera     varchar(50)  NOT NULL,
	cod_guarani     varchar(50)  NOT NULL DEFAULT '',   -- el código de actividad SIU, si vino
	plan_guarani    varchar(50)  NOT NULL DEFAULT '',   -- el plan SIU ('688')
	version_guarani varchar(50)  NOT NULL DEFAULT '',   -- su versión ('1')
	materia         varchar(255) NOT NULL,              -- el nombre, como vino
	dni             varchar(20)  NOT NULL,
	-- La materia del catálogo (ver arriba); NULL si no está.
	id_materia      integer,
	actualizado     timestamp NOT NULL DEFAULT now(),
	actualizado_por varchar(100)
);
CREATE INDEX IF NOT EXISTS materias_inscripto_envio ON huayca.materias_inscripto (ano_academico, envio);
CREATE INDEX IF NOT EXISTS materias_inscripto_carrera ON huayca.materias_inscripto (ano_academico, cod_carrera);
CREATE INDEX IF NOT EXISTS materias_inscripto_materia ON huayca.materias_inscripto (ano_academico, id_materia);
