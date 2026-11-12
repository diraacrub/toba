<?php
/**
 * Como entra cada usuario: con la cuenta Huayca o con el legajo y la clave de
 * Toba de siempre (tabla huayca.ingresos_cuenta, deploy/sql/ingresos_cuenta.sql).
 *
 * Sirve para empujar el paso a la cuenta Huayca antes de que la clave vieja deje
 * de valer (exclusivo_desde en keycloak.ini):
 *  - catedras_autenticacion_keycloak anota cada ingreso;
 *  - quien entra con la clave vieja ve despues una pantalla que le pide activar
 *    la cuenta Huayca (pant_login), distinta si ya entro alguna vez con ella;
 *  - Administracion > Cuentas Huayca lista quien sigue con la clave vieja.
 *
 * Anotar nunca frena un ingreso: si la tabla no esta o la base falla, se loguea
 * y se sigue.
 *
 * Archivo en ASCII.
 */
class catedras_ingresos
{
	const TABLA = 'huayca.ingresos_cuenta';
	const MARCA = 'aviso_legado';	// en $_SESSION[catedras_huayca_sso::SESION]

	/** Anota un ingreso. $como: 'huayca' o 'legado'. */
	static function registrar($usuario, $como)
	{
		$col = ($como === 'huayca') ? 'huayca' : 'legado';
		try {
			$db = toba::instancia()->get_db();
			$db->ejecutar('INSERT INTO ' . self::TABLA . " (usuario, ultimo_$col, ingresos_$col) VALUES ("
				. $db->quote((string) $usuario) . ', now(), 1)'
				. " ON CONFLICT (usuario) DO UPDATE SET ultimo_$col = now(),"
				. " ingresos_$col = " . self::TABLA . ".ingresos_$col + 1");
		} catch (Exception $e) {
			toba::logger()->error('INGRESOS: no se pudo anotar el ingreso de ' . $usuario . ': ' . $e->getMessage());
		}
	}

	/** La fila del usuario, o null. */
	static function de($usuario)
	{
		try {
			$db = toba::instancia()->get_db();
			$filas = $db->consultar('SELECT * FROM ' . self::TABLA . ' WHERE usuario = ' . $db->quote((string) $usuario));
			return $filas ? $filas[0] : null;
		} catch (Exception $e) {
			toba::logger()->error('INGRESOS: no se pudo leer ' . $usuario . ': ' . $e->getMessage());
			return null;
		}
	}

	/** true si alguna vez entro con la cuenta Huayca. */
	static function uso_huayca($usuario)
	{
		$fila = self::de($usuario);
		return $fila !== null && ! empty($fila['ultimo_huayca']);
	}

	/** Dias que faltan hasta el primer dia solo Huayca, o null si no hay transicion. */
	static function dias_restantes()
	{
		$desde = catedras_autenticacion_keycloak::exclusivo_desde();
		if ($desde === null) {
			return null;
		}
		return (int) (new DateTimeImmutable('today'))->diff($desde)->format('%r%a');
	}

	// --- La pantalla que sigue al ingreso con la clave vieja (pant_login) ---

	static function marcar_aviso()
	{
		$_SESSION[catedras_huayca_sso::SESION][self::MARCA] = true;
	}

	static function hay_aviso()
	{
		return ! empty($_SESSION[catedras_huayca_sso::SESION][self::MARCA]);
	}

	static function consumir_aviso()
	{
		unset($_SESSION[catedras_huayca_sso::SESION][self::MARCA]);
	}
}
