-- Como entra cada usuario de catedras: con la cuenta Huayca o con el legajo y
-- la clave de Toba de siempre. Lo escribe catedras_ingresos (en cada ingreso) y
-- lo leen la pantalla que sigue al ingreso con la clave vieja y
-- Administracion > Cuentas Huayca.
--
-- Va en el esquema de negocio (huayca), no en el de la instancia de Toba:
-- "bin/toba proyecto regenerar" no lo toca. Se corre una vez por base:
--   psql ... -d toba_3_4 -f deploy/sql/ingresos_cuenta.sql
CREATE TABLE IF NOT EXISTS huayca.ingresos_cuenta (
	usuario          varchar(60) PRIMARY KEY,   -- apex_usuario.usuario (el legajo)
	ultimo_huayca    timestamp,
	ingresos_huayca  integer NOT NULL DEFAULT 0,
	ultimo_legado    timestamp,
	ingresos_legado  integer NOT NULL DEFAULT 0,
	desde            timestamp NOT NULL DEFAULT now()   -- primer ingreso registrado
);
COMMENT ON TABLE huayca.ingresos_cuenta IS 'Ingresos a catedras por usuario: cuenta Huayca o clave vieja de Toba (desde 2026-11-12)';
