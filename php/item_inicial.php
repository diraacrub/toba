<?php
/**
 * Pantalla de inicio de catedras.
 *
 * Saludo, la guia de uso que corresponde a cada perfil (www/ayuda/: docentes,
 * departamentos, Secretaria Academica; admin ve las tres), el acceso a Gestion
 * Academica (departamentos y Secretaria) y las fechas de las Obligaciones
 * Docentes (Anexo I del Calendario Academico CRUB 1719/25).
 *
 * Los estilos estan en www/css/huayca.css (seccion "Inicio"), con el resto del
 * tema. Archivo en ASCII: los acentos van como entidades HTML.
 */

require_once __DIR__ . '/extension_toba/catedras_perfiles.php';

$perfiles = catedras_perfiles::actual();
$nombre_completo = texto_plano(toba::usuario()->get_nombre());

$es_admin = $perfiles->es_admin();
$es_docente = $perfiles->es_docente();
$es_depto = $perfiles->es_depto();
$es_sac = $perfiles->es_sac();
$ve_obligaciones = $es_admin || $es_docente || $es_depto || $es_sac;

// Guias de uso (www/ayuda/), segun el perfil
$guias = array();
if ($es_admin || $es_docente) { $guias['docentes.html'] = 'Gu&iacute;a para docentes'; }
if ($es_admin || $es_depto) { $guias['departamentos.html'] = 'Gu&iacute;a para departamentos'; }
if ($es_admin || $es_sac) { $guias['sac.html'] = 'Gu&iacute;a para la Secretar&iacute;a Acad&eacute;mica'; }
$url_ayuda = toba_recurso::url_proyecto() . '/ayuda/';

echo '<div class="hy-inicio">';

// --- Saludo ---
echo '<header class="hy-inicio-saludo">';
echo '<p class="hy-inicio-sistema">C&aacute;tedras &middot; CRUB</p>';
echo '<h1>Hola, ' . $nombre_completo . '</h1>';
echo '<p class="hy-inicio-bajada">Programas, planificaciones e informes de c&aacute;tedra. Eleg&iacute; una opci&oacute;n del men&uacute; para empezar.</p>';
echo '</header>';

if ($guias) {
	// --- Guia de uso ---
	echo '<section class="hy-tarjeta hy-instructivo">';
	echo '<div>';
	echo '<h2>C&oacute;mo usar C&aacute;tedras</h2>';
	echo '<p>Una gu&iacute;a corta para presentar, revisar y aprobar programas, planificaciones e informes.</p>';
	echo '</div>';
	echo '<div class="hy-guias">';
	foreach ($guias as $archivo => $titulo) {
		echo '<a href="' . $url_ayuda . $archivo . '" target="_blank" rel="noopener" class="hy-boton-primario">' . $titulo . ' <span aria-hidden="true">&#8599;</span></a>';
	}
	echo '</div>';
	echo '</section>';
}

if ($es_admin || $es_depto || $es_sac || $perfiles->es_docente_crub()) {
	// --- Gestion Academica: la otra aplicacion de Huayca, con la misma cuenta.
	// Un departamento tiene ahi redesignaciones, ayudantes estudiantes y
	// licencias; Secretaria, su panel con todo lo pendiente; un docente, Mis
	// catedras. Secretaria, departamentos y docentes ya empiezan alla al entrar con la
	// cuenta Huayca (catedras_perfiles): la tarjeta es para volver.
	$url_gestion = catedras_perfiles::URL_GESTION;
	if ($es_sac || $es_admin) {
		$texto_gestion = 'El panel de Secretar&iacute;a: lo pendiente de redesignaciones, conjuntos de materias, ayudantes estudiantes y programas del a&ntilde;o.';
		$boton_gestion = 'Panel de Secretar&iacute;a';
	} elseif ($es_depto) {
		$texto_gestion = 'Las redesignaciones, los ayudantes estudiantes y las licencias a renovar de tu departamento.';
		$boton_gestion = 'Ir a Gesti&oacute;n Acad&eacute;mica';
	} else {
		$url_gestion .= 'mis-catedras';
		$texto_gestion = 'Mis c&aacute;tedras: tus materias del a&ntilde;o, con sus documentos, las fechas de entrega y el equipo.';
		$boton_gestion = 'Ir a Mis c&aacute;tedras';
	}
	echo '<section class="hy-tarjeta hy-instructivo">';
	echo '<div>';
	echo '<h2>Gesti&oacute;n Acad&eacute;mica</h2>';
	echo '<p>' . $texto_gestion . ' Se entra con la misma cuenta Huayca.</p>';
	echo '</div>';
	echo '<div class="hy-guias">';
	echo '<a href="' . $url_gestion . '" target="_blank" rel="noopener" class="hy-boton-primario">' . $boton_gestion . ' <span aria-hidden="true">&#8599;</span></a>';
	echo '</div>';
	echo '</section>';
}

if ($ve_obligaciones) {
	// --- Obligaciones docentes ---
	echo '<section class="hy-tarjeta">';
	echo '<h2>Obligaciones docentes</h2>';
	echo '<p class="hy-fuente">Anexo I &middot; Calendario Acad&eacute;mico CRUB 1719/25</p>';
	echo '<p>Se solicita a los/as Profesores/as encargados/as de c&aacute;tedra el estricto cumplimiento de las fechas establecidas.</p>';

	echo '<ul class="hy-fechas">';
	echo '<li>'
		. '<span class="hy-fecha">18 de febrero &ndash; 6 de marzo</span>'
		. '<span class="hy-que"><strong>Programas y planificaciones</strong> &middot; asignaturas del primer cuatrimestre y anuales</span>'
		. '</li>';
	echo '<li>'
		. '<span class="hy-fecha">27 de julio &ndash; 7 de agosto</span>'
		. '<span class="hy-que"><strong>Programas y planificaciones</strong> &middot; asignaturas del segundo cuatrimestre</span>'
		. '</li>';
	echo '<li>'
		. '<span class="hy-fecha">Hasta el 11 de diciembre</span>'
		. '<span class="hy-que"><strong>Informe de c&aacute;tedra</strong> &middot; asignaturas de 1&deg; y 2&deg; cuatrimestre y anuales</span>'
		. '</li>';
	echo '</ul>';

	echo '<p class="hy-nota">Programas y planificaciones se presentan a trav&eacute;s de Huayca a Directores/as o Delegados/as Departamentales.</p>';
	echo '</section>';
}

echo '</div>';
?>
