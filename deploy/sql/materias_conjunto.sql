-- Los dictados conjuntos de cada año: qué materias (de qué carreras y planes)
-- se dictan juntas. Vienen de Mocovi, como la hoja ConjuntosMaterias de la
-- planilla: una fila por dictado y materia. Lo escribe POST /rest/conjuntos
-- (php/informes/materias_conjunto.php) cuando Secretaría los trae desde deptos:
-- cada vez, los del año se reemplazan enteros (queda un renglón en
-- huayca.movimientos). Lo lee GET /rest/conjuntos: deptos arma con esto las
-- salidas de campo de un conjunto (y Redesignaciones, los conjuntos de Mocovi).
--
-- Va en el esquema de negocio (huayca), no en el de la instancia de Toba:
-- "bin/toba proyecto regenerar" no lo toca. Se corre una vez por base:
--   psql ... -d toba_3_4 -f deploy/sql/materias_conjunto.sql
CREATE TABLE IF NOT EXISTS huayca.materias_conjunto (
	id_materia_conjunto serial PRIMARY KEY,
	ano_academico   integer NOT NULL,
	-- Las columnas de Mocovi, como texto tal cual (los ceros de adelante de los
	-- códigos importan). Ordenanza es plan_mocovi.
	id_conjunto     varchar(20)  NOT NULL,   -- el dictado en Mocovi
	conjunto        varchar(255) NOT NULL,   -- su nombre
	periodo         varchar(50)  NOT NULL DEFAULT '',   -- '1CUAT', '2CUAT', 'ANUAL'
	materia         varchar(255) NOT NULL,
	cod_guarani     varchar(50)  NOT NULL DEFAULT '',   -- Cod SIU
	cod_carrera     varchar(50)  NOT NULL,
	plan_mocovi     varchar(50)  NOT NULL,
	-- La materia del catálogo (cod_guarani, cod_carrera, plan_mocovi); NULL si
	-- no está.
	id_materia      integer,
	actualizado     timestamp NOT NULL DEFAULT now(),
	actualizado_por varchar(100)
);
CREATE INDEX IF NOT EXISTS materias_conjunto_anio ON huayca.materias_conjunto (ano_academico, id_conjunto);
