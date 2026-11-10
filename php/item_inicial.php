<?php
/**
 * Pantalla de inicio de catedras.
 *
 * Saludo, la guia de uso que corresponde a cada perfil (www/ayuda/: docentes,
 * departamentos, Secretaria Academica; admin ve las tres) y las fechas de las
 * Obligaciones Docentes (Anexo I del Calendario Academico CRUB 1719/25).
 *
 * Los estilos estan en www/css/huayca.css (seccion "Inicio"), con el resto del
 * tema. Archivo en ASCII: los acentos van como entidades HTML.
 */

$perfil_usuario = toba::usuario()->get_perfiles_funcionales();
$nombre_completo = texto_plano(toba::usuario()->get_nombre());

$perfil_usuario_lower = array_map('strtolower', $perfil_usuario);
$perfiles_deptos = array('biologiageneral','botanica','didactica','ecologia','educacionfisica','enfermeria','estadistica','explotacionderecursosacuaticos','fisica','geologiaypetroleo','idiomasextranjerosconpropositosespecificos','ingenieriacivil','matematica','politicaeducacional','psicologia','quimica','zoologia');

$es_admin = in_array("admin", $perfil_usuario_lower);
$es_docente = in_array("docente", $perfil_usuario_lower) || in_array("docentefadel", $perfil_usuario_lower);
$es_depto = count(array_intersect($perfil_usuario_lower, $perfiles_deptos)) > 0;
$es_sac = count(array_intersect($perfil_usuario_lower, array('sac', 'saccrub', 'sacfadel'))) > 0;
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
