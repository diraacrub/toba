<?php
/**
 * Administracion > Cuentas Huayca
 *
 * Quien sigue entrando con el legajo y la clave vieja de Toba, para empujarlo a
 * la cuenta Huayca antes de que deje de valer (exclusivo_desde en keycloak.ini).
 * Lee huayca.ingresos_cuenta (catedras_ingresos), que se anota en cada ingreso
 * desde el 12/11/2026: lo que paso antes no esta.
 *
 *   - Siguen con la clave vieja: su ultimo ingreso fue con legajo y clave.
 *   - Ya entran con la cuenta Huayca: su ultimo ingreso fue con ella.
 *   - Sin ingresos registrados: todavia no entraron desde que se anota.
 *
 * Solo usuarios de catedras con DNI cargado (los demas no pueden tener cuenta
 * Huayca). Solo para el perfil admin (permiso del item y control aca).
 *
 * Estilos: los de Importar usuarios (www/css/huayca.css). Archivo en ASCII.
 */

class catedras_cuentas_huayca
{
	static function h($texto)
	{
		return htmlspecialchars((string) $texto, ENT_QUOTES, 'ISO-8859-1');
	}

	static function fecha($ts)
	{
		return $ts ? date('d/m/Y H:i', strtotime($ts)) : '';
	}

	/** Usuarios de catedras con DNI y como entraron. */
	static function filas()
	{
		$db = toba::instancia()->get_db();
		return $db->consultar("
			SELECT u.usuario, u.nombre, u.ciu AS dni,
			       i.ultimo_huayca, i.ingresos_huayca, i.ultimo_legado, i.ingresos_legado
			  FROM apex_usuario u
			  LEFT JOIN huayca.ingresos_cuenta i ON i.usuario = u.usuario
			 WHERE u.ciu ~ '^[0-9. ]{6,12}$'
			   AND EXISTS (SELECT 1 FROM apex_usuario_proyecto p WHERE p.usuario = u.usuario AND p.proyecto = 'catedras')
			 ORDER BY i.ultimo_legado DESC NULLS LAST, u.nombre");
	}

	static function ejecutar()
	{
		echo '<div class="hy-inicio hy-import">';
		echo '<header class="hy-inicio-saludo">';
		echo '<p class="hy-inicio-bajada">Qui&eacute;n sigue entrando con el legajo y la clave vieja, para pedirle que pase a su cuenta Huayca'
			. ' antes de que deje de valer. Se registra desde el 12/11/2026.</p>';
		echo '</header>';

		$perfiles = array_map('strtolower', toba::usuario()->get_perfiles_funcionales());
		if (!in_array('admin', $perfiles)) {
			echo '<div class="hy-import-aviso hy-import-aviso-error">Esta secci&oacute;n es solo para administradores.</div></div>';
			return;
		}
		try {
			$filas = self::filas();
		} catch (Exception $e) {
			toba::logger()->error('CUENTAS HUAYCA: ' . $e->getMessage());
			echo '<div class="hy-import-aviso hy-import-aviso-error">No se pudo leer el registro de ingresos'
				. ' (falta correr deploy/sql/ingresos_cuenta.sql?).</div></div>';
			return;
		}

		$grupos = array('legado' => array(), 'huayca' => array(), 'nada' => array());
		foreach ($filas as $f) {
			if ($f['ultimo_legado'] && (!$f['ultimo_huayca'] || $f['ultimo_legado'] > $f['ultimo_huayca'])) {
				$grupos['legado'][] = $f;
			} elseif ($f['ultimo_huayca']) {
				$grupos['huayca'][] = $f;
			} else {
				$grupos['nada'][] = $f;
			}
		}

		echo '<section class="hy-tarjeta">';
		echo '<div class="hy-import-resumen">';
		echo '<div class="hy-chip hy-chip-problema"><strong>' . count($grupos['legado']) . '</strong> siguen con la clave vieja</div>';
		echo '<div class="hy-chip hy-chip-nuevo"><strong>' . count($grupos['huayca']) . '</strong> ya entran con la cuenta Huayca</div>';
		echo '<div class="hy-chip hy-chip-igual"><strong>' . count($grupos['nada']) . '</strong> sin ingresos registrados</div>';
		echo '</div>';

		self::tabla('Siguen con la clave vieja', $grupos['legado'], true);
		self::tabla('Ya entran con la cuenta Huayca', $grupos['huayca'], false);
		self::tabla('Sin ingresos registrados', $grupos['nada'], false);
		echo '<p class="hy-nota">Para que activen la cuenta: la pantalla que ven despu&eacute;s de entrar con la clave vieja les manda el mail con un clic.'
			. ' Tambi&eacute;n pueden hacerlo desde &laquo;&iquest;Primera vez? Activ&aacute; tu cuenta&raquo; en el login.</p>';
		echo '</section>';
		echo '</div>';
	}

	static function tabla($titulo, $lista, $abierta)
	{
		echo '<details class="hy-import-grupo"' . ($abierta ? ' open' : '') . '><summary>' . $titulo . ' (' . count($lista) . ')</summary>';
		if (!$lista) {
			echo '<p class="hy-nota" style="padding: 0 14px">Nadie.</p></details>';
			return;
		}
		echo '<table class="hy-import-tabla"><thead><tr><th>Usuario</th><th>Nombre</th><th>DNI</th>'
			. '<th>&Uacute;ltimo con la clave vieja</th><th>Veces</th><th>&Uacute;ltimo con la cuenta Huayca</th></tr></thead><tbody>';
		foreach ($lista as $f) {
			echo '<tr><td>' . self::h($f['usuario']) . '</td><td>' . self::h($f['nombre']) . '</td><td>' . self::h($f['dni']) . '</td>'
				. '<td>' . self::fecha($f['ultimo_legado']) . '</td><td>' . ($f['ingresos_legado'] ? (int) $f['ingresos_legado'] : '') . '</td>'
				. '<td>' . ($f['ultimo_huayca'] ? self::fecha($f['ultimo_huayca']) : '&mdash;') . '</td></tr>';
		}
		echo '</tbody></table></details>';
	}
}

catedras_cuentas_huayca::ejecutar();
