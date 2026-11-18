<?php
require_once dirname(__FILE__) . '/importar_programas.php';
require_once dirname(__FILE__) . '/actualizar_catedras.php';
require_once dirname(__FILE__) . '/../informes/materias_equipo.php';

/**
 * El informe de Mocovi de un anio, en una sola pasada (POST /rest/mocovi-anio).
 *
 * Secretaria sube en deptos (/deptos/programas) el informe "salida" de Mocovi:
 * el equipo de cada materia, con su rol. deptos arma con eso lo que va en cada
 * programa (resolviendo los dobles) y lo manda junto; aca, en una sola
 * transaccion y en este orden:
 *
 *   1. El equipo de cada materia (huayca.materias_equipo): materias_equipo.
 *   2. El alta: los programas que faltan, cada uno con su planificacion y su
 *      informe, sin publicar (catedras_importar_programas).
 *   3. Los programas que ya existen: responsable y equipo, si estan en estado
 *      docente y en plazo (catedras_actualizar::*_programas).
 *   4. El responsable vigente de cada programa que ya existe: siempre, aunque
 *      este firmado (catedras_actualizar::*_vigente).
 *   5. Los informes: se crean los que faltan (sin publicar) y se actualiza su
 *      responsable si estan en estado docente y en plazo
 *      (catedras_actualizar::*_informes).
 *
 * No se sobrescribe contenido: solo se crean documentos y se cambian el
 * responsable y el equipo. Del 3 al 5, solo desde catedras_actualizar::DESDE.
 *
 * Simular hace exactamente lo mismo y al final deshace todo (ROLLBACK): lo que
 * devuelve es lo que pasaria, con los pasos encadenados como al confirmar (por
 * ejemplo, el informe de un programa nuevo). Sin bloquear tablas ni adelantar
 * secuencias. Mandar lo mismo dos veces no cambia nada la segunda.
 *
 * Archivo en ASCII.
 */
class catedras_mocovi
{
	static function db()
	{
		return toba::db('catedras');
	}

	/**
	 * array('equipo' => plan, 'alta' => plan + 'creados', 'programas' => plan,
	 * 'vigente' => plan, 'informes' => plan + 'creados', 'vigentes' => los del
	 * anio despues de aplicar, 'vigentes_cambios' => ver cambios_vigentes).
	 * Levanta la excepcion de la base si algo falla (y no queda nada).
	 */
	static function aplicar($ano, $equipo, $alta, $programas, $informes, $usuario, $simular)
	{
		$db = self::db();
		$r = array();
		$db->abrir_transaccion();
		try {
			if (!$simular) {
				// Los ids nuevos son el siguiente al mas alto: nadie puede tomar
				// el mismo numero en el medio.
				$db->ejecutar('LOCK TABLE ' . materias_equipo::TABLA
					. ', programas, planificaciones, informes IN SHARE ROW EXCLUSIVE MODE');
			}
			$r['equipo'] = $equipo ? materias_equipo::aplicar_en($db, $ano, $equipo, $usuario) : null;
			$antes = catedras_actualizar::vigentes_del_anio($ano);

			$r['alta'] = catedras_importar_programas::planificar($alta);
			$r['alta']['creados'] = catedras_importar_programas::aplicar_en($db, $r['alta'], $usuario);

			$r['programas'] = catedras_actualizar::aplicar_programas_en($db, $programas, $usuario);
			$r['vigente'] = catedras_actualizar::aplicar_vigente_en($db, $programas, $usuario);
			$r['informes'] = catedras_actualizar::aplicar_informes_en($db, $informes, $usuario);
			$r['vigentes'] = catedras_actualizar::vigentes_del_anio($ano);
			$r['vigentes_cambios'] = self::cambios_vigentes($antes, $r['vigentes']);

			if ($simular) {
				$db->abortar_transaccion();
			} else {
				catedras_importar_programas::adelantar_secuencias($db);
				$db->cerrar_transaccion();
			}
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('MOCOVI ' . $ano . ($simular ? ' (simulado)' : '') . ': ' . $e->getMessage());
			throw $e;
		}
		if (!$simular) {
			toba::logger()->notice('MOCOVI ' . $ano . ' por ' . $usuario . ': ' . count($r['alta']['nuevos']) . ' programas nuevos, '
				. count($r['programas']['actualizar']) . ' actualizados, ' . count($r['vigente']['cambia']) . ' con otro responsable vigente, '
				. count($r['informes']['creados']) . ' informes creados, ' . count($r['informes']['actualizar']) . ' con otro responsable');
		}
		return $r;
	}

	/**
	 * Los programas que ya estaban y cambiaron de responsable vigente (por el
	 * paso 3, si el programa estaba en borrador, o por el 4, si no): lo que
	 * deptos necesita para las salidas de campo. Cada uno: id_programa,
	 * id_materia, periodo, antes y despues (legajo, dni).
	 */
	static function cambios_vigentes($antes, $despues)
	{
		$por_id = array();
		foreach ($antes as $a) {
			$por_id[$a['id_programa']] = $a;
		}
		$salida = array();
		foreach ($despues as $d) {
			$a = $por_id[$d['id_programa']] ?? null;
			if ($a && ($a['legajo'] !== $d['legajo'] || $a['dni'] !== $d['dni'])) {
				$salida[] = array('id_programa' => $d['id_programa'], 'id_materia' => $d['id_materia'],
					'periodo' => $d['periodo'], 'antes' => array('legajo' => $a['legajo'], 'dni' => $a['dni']),
					'despues' => array('legajo' => $d['legajo'], 'dni' => $d['dni']));
			}
		}
		return $salida;
	}

	//-------------------------------------------------------------------------
	// Publicar
	//-------------------------------------------------------------------------

	/**
	 * Publica o despublica programas (con su planificacion) e informes. Solo
	 * desde catedras_actualizar::DESDE; despublicar, solo en estado docente (lo
	 * enviado ya lo vieron). Devuelve array('cambiados' => [{tipo, id}],
	 * 'sin_cambios' => [...], 'no_se_tocan' => [{tipo, id, motivo}]).
	 */
	static function publicar($programas, $informes, $publicar, $usuario)
	{
		$db = self::db();
		$r = array('cambiados' => array(), 'sin_cambios' => array(), 'no_se_tocan' => array());
		$consultas = array(
			'programa' => array($programas, "SELECT p.id_programa AS id, trim(p.ano_academico) AS ano, p.estado, p.publicado
				FROM programas p WHERE p.id_programa IN (%s)", 'programas', 'id_programa'),
			'informe' => array($informes, "SELECT i.id_informe AS id, trim(p.ano_academico) AS ano, i.estado_informe AS estado,
				i.publicado FROM informes i JOIN programas p ON p.id_programa = i.id_prog_informe
				WHERE i.id_informe IN (%s)", 'informes', 'id_informe'),
		);
		$db->abrir_transaccion();
		try {
			foreach ($consultas as $tipo => $c) {
				list($ids, $sql, $tabla, $columna) = $c;
				$ids = array_values(array_unique(array_map('intval', $ids)));
				if (!$ids) {
					continue;
				}
				$hay = array();
				foreach ($db->consultar(sprintf($sql, implode(', ', $ids))) as $f) {
					$hay[(int) $f['id']] = $f;
				}
				$cambiar = array();
				foreach ($ids as $id) {
					$f = $hay[$id] ?? null;
					$publicado = $f ? in_array($f['publicado'], array(true, 't', '1', 1), true) : null;
					if (!$f) {
						$r['no_se_tocan'][] = array('tipo' => $tipo, 'id' => $id, 'motivo' => 'no existe');
					} elseif ((int) $f['ano'] < catedras_actualizar::DESDE) {
						$r['no_se_tocan'][] = array('tipo' => $tipo, 'id' => $id, 'motivo' => 'es de ' . $f['ano']);
					} elseif ($publicado === (bool) $publicar) {
						$r['sin_cambios'][] = array('tipo' => $tipo, 'id' => $id);
					} elseif (!$publicar && $f['estado'] !== 'docente') {
						$r['no_se_tocan'][] = array('tipo' => $tipo, 'id' => $id, 'motivo' => 'ya se envi&oacute;');
					} else {
						$cambiar[] = $id;
						$r['cambiados'][] = array('tipo' => $tipo, 'id' => $id);
					}
				}
				if ($cambiar) {
					$en = implode(', ', $cambiar);
					$db->ejecutar("UPDATE $tabla SET publicado = " . ($publicar ? 'true' : 'false')
						. ', publicado_cambio = now(), publicado_por = ' . $db->quote(substr($usuario, 0, 100))
						. " WHERE $columna IN ($en)");
					$que = $publicar ? 'Publicado' : 'Despublicado';
					$db->ejecutar("INSERT INTO movimientos (nombre_tabla, id_tabla, tipo_movimiento, usuario_movimiento, observaciones, estado_mov) "
						. "SELECT '$tabla', x, " . $db->quote($que) . ', ' . $db->quote($usuario) . ', '
						. $db->quote($que . ' desde deptos') . ', NULL FROM unnest(ARRAY[' . $en . ']) AS x');
				}
			}
			$db->cerrar_transaccion();
		} catch (Exception $e) {
			$db->abortar_transaccion();
			toba::logger()->error('PUBLICAR: ' . $e->getMessage());
			throw $e;
		}
		return $r;
	}
}
