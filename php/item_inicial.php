<?php
    // We use HTML Entities (e.g., &eacute;) to guarantee compatibility with SIU-Toba encoding
    
    echo '<style>
        .container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            text-align: left; /* Ensures everything starts from the left */
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: left; /* Fixes centering */
        }
        .card h2 {
            color: #001f3f;
            font-size: 20px;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .card h3 {
            color: #004080;
            font-size: 16px;
            margin: 15px 0 5px 0;
        }
        .card p, .card li {
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
        }
        .logo {
            text-align: center; /* Logo remains centered */
            margin-bottom: 20px;
        }
        .link-button {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #001f3f;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        ul {
            margin: 10px 0;
            padding-left: 25px;
        }
    </style>';

echo '<div class="container">';


    $perfil_usuario = toba::usuario()->get_perfiles_funcionales();
    $nombre_completo = toba::usuario()->get_nombre();
    $url_instructivo = "https://docs.google.com/document/d/e/2PACX-1vTryfOO8lS7F6OgRxtef33SBe59Mz_pvmH8vXMCQt5t6GxhMQDWGbwwnUg3fZhzn2DbOyf_QOKXFLqx/pub";

    $perfil_usuario_lower = array_map('strtolower', $perfil_usuario);
    $perfiles_deptos = array('biologiageneral','botanica','didactica','ecologia','educacionfisica','enfermeria','estadistica','explotacionderecursosacuaticos','fisica','geologiaypetroleo','idiomasextranjerosconpropositosespecificos','ingenieriacivil','matematica','politicaeducacional','psicologia','quimica','zoologia');

    $es_admin = in_array("admin", $perfil_usuario_lower);
    $es_docente = in_array("docente", $perfil_usuario_lower) || in_array("docentefadel", $perfil_usuario_lower);
    $es_depto = count(array_intersect($perfil_usuario_lower, $perfiles_deptos)) > 0;

    // --- CARD 1: USUARIO & INSTRUCTIVO ---
    echo '<div class="card">';
    echo '<h2>' . $nombre_completo . '</h2>';

    if ($es_admin || $es_docente || $es_depto) {
        // Text using HTML entities for accents
        echo '<p>Pod&eacute;s acceder al <strong>instructivo de carga</strong> para el sistema Huayca a trav&eacute;s del siguiente enlace:</p>';
        echo '<a href="'.$url_instructivo.'" target="_blank" class="link-button">Abrir Instructivo</a>';
    }
    echo '</div>';

    // --- CARD 2: OBLIGACIONES DOCENTES ---
    if ($es_admin || $es_docente || $es_depto) {
        echo '<div class="card">';
        echo '<h2>Oblicaciones Docentes (ANEXO I - Calendario Acad&eacute;mico CRUB 1719/25)</h2>';
        echo '<p>Se solicita a los/as Profesores/as encargados/as de c&aacute;tedra el estricto cumplimiento de las fechas establecidas.</p>';
        
        echo '<h3>Presentaci&oacute;n de Programas y Planificaciones Acad&eacute;micas</h3>';
        echo '<p>A trav&eacute;s de sistema Huayca a Directores/as o Delegados/as Departamentales:</p>';
        echo '<ul>
                <li><strong>Asignaturas del Primer Cuatrimestre y Anuales:</strong> Del 18 de febrero al 6 de marzo</li>
                <li><strong>Asignaturas del Segundo Cuatrimestre:</strong> Del 27 de julio al 7 de agosto</li>
              </ul>';


        echo '<h3>Presentaci&oacute;n Informe de C&aacute;tedra</h3>';
        echo '<p>A trav&eacute;s de sistema Huayca (Asignaturas 1&deg;, 2&deg; Cuatrimestre y Anuales):</p>';
        echo '<ul><li><strong>Hasta el 11 de diciembre</strong></li></ul>';
        echo '</div>';
    }

echo '</div>';
?>
