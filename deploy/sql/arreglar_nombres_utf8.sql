-- Repara nombres con la N con tilde y las tildes rotas (UTF-8 guardado en una base LATIN1).
--
-- Origen: php/extension_toba/comandos/poblar_usuarios.php cargaba
-- apex_usuario.nombre desde un CSV en UTF-8 sin convertir (ya corregido). Una
-- "N con tilde" quedaba como U+00C3 + U+0091, una "O con tilde" como U+00C3 + U+0093, etc. Cada firma
-- y cada comentario de esas personas copia el nombre, asi que el error se
-- propaga a programas, planificaciones e informes.
--
-- Que hace: reemplaza SOLO los pares rotos (U+00C3 o U+00C2 seguidos de un caracter
-- U+0080..U+00BF) por el caracter correcto. Las tildes que ya estan bien en
-- el mismo texto (p. ej. la de "electronicamente" en la firma) no se tocan.
-- No toca las tablas de respaldo (programas_11, informes_2, ...).
--
-- Archivo en ASCII: los caracteres se arman con chr() y decode(), asi no
-- depende de la codificacion con que se lea.
--
-- Uso (en huayca, como postgres):
--   sudo -u postgres psql -d toba_3_4 -f deploy/sql/arreglar_nombres_utf8.sql
-- Todo va en una transaccion: si algo falla no cambia nada. Al final muestra
-- cuantas filas quedan rotas (tiene que dar 0 en cada tabla).

BEGIN;

CREATE OR REPLACE FUNCTION pg_temp.arreglar_utf8(t text) RETURNS text AS $$
DECLARE
	b int;
	r text := t;
BEGIN
	IF r IS NULL THEN
		RETURN r;
	END IF;
	FOR b IN 128..191 LOOP
		-- chr(195) = U+00C3, chr(194) = U+00C2: primer byte de la secuencia UTF-8
		r := replace(r, chr(195) || chr(b), convert_from(decode('c3' || to_hex(b), 'hex'), 'UTF8'));
		r := replace(r, chr(194) || chr(b), convert_from(decode('c2' || to_hex(b), 'hex'), 'UTF8'));
	END LOOP;
	RETURN r;
END
$$ LANGUAGE plpgsql IMMUTABLE;

-- Filtro: tiene al menos un par roto
CREATE OR REPLACE FUNCTION pg_temp.roto(t text) RETURNS boolean AS $$
	SELECT t ~ ('[' || chr(194) || chr(195) || '][' || chr(128) || '-' || chr(191) || ']')
$$ LANGUAGE sql IMMUTABLE;

UPDATE desarrollo.apex_usuario SET nombre = pg_temp.arreglar_utf8(nombre) WHERE pg_temp.roto(nombre);

UPDATE huayca.programas SET firma_doc = pg_temp.arreglar_utf8(firma_doc) WHERE pg_temp.roto(firma_doc);
UPDATE huayca.programas SET comentarios = pg_temp.arreglar_utf8(comentarios) WHERE pg_temp.roto(comentarios);

UPDATE huayca.planificaciones SET firma_doc_planif = pg_temp.arreglar_utf8(firma_doc_planif) WHERE pg_temp.roto(firma_doc_planif);
UPDATE huayca.planificaciones SET comentarios_planif = pg_temp.arreglar_utf8(comentarios_planif) WHERE pg_temp.roto(comentarios_planif);

UPDATE huayca.informes SET firma_doc_inf = pg_temp.arreglar_utf8(firma_doc_inf) WHERE pg_temp.roto(firma_doc_inf);
UPDATE huayca.informes SET comentarios_inf = pg_temp.arreglar_utf8(comentarios_inf) WHERE pg_temp.roto(comentarios_inf);

-- Control: tiene que dar 0 en todas
SELECT 'apex_usuario.nombre' AS columna, count(*) AS siguen_rotos FROM desarrollo.apex_usuario WHERE pg_temp.roto(nombre)
UNION ALL SELECT 'programas.firma_doc', count(*) FROM huayca.programas WHERE pg_temp.roto(firma_doc)
UNION ALL SELECT 'programas.comentarios', count(*) FROM huayca.programas WHERE pg_temp.roto(comentarios)
UNION ALL SELECT 'planificaciones.firma_doc_planif', count(*) FROM huayca.planificaciones WHERE pg_temp.roto(firma_doc_planif)
UNION ALL SELECT 'planificaciones.comentarios_planif', count(*) FROM huayca.planificaciones WHERE pg_temp.roto(comentarios_planif)
UNION ALL SELECT 'informes.firma_doc_inf', count(*) FROM huayca.informes WHERE pg_temp.roto(firma_doc_inf)
UNION ALL SELECT 'informes.comentarios_inf', count(*) FROM huayca.informes WHERE pg_temp.roto(comentarios_inf);

COMMIT;
