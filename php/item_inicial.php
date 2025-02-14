<?php
    // IMPORTANTE:
    // 1. No se utiliza header() para definir la codificación, ya que puede generar el warning si ya se ha iniciado la salida.
    // 2. Asegúrate de que este archivo se guarde en UTF-8 sin BOM.
    // 3. Verifica que en el <head> de tu plantilla se incluya: <meta charset="UTF-8">
    // Si no es posible, se incluye la meta tag a continuación:
    echo '<meta charset="UTF-8">';

    echo '<style>
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: left;
        }
        .card h2 {
            color: #001f3f;
            font-size: 18px;
            margin-top: 0;
        }
        .card p {
            color: #001f3f;
            font-size: 14px;
            margin: 0 0 10px;
        }
        .logo {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }
    </style>';

echo '<div class="container">';

    echo '<div class="logo">';
        echo toba_recurso::imagen_proyecto("logo_grande.gif", true);
    echo '</div>';

    $perfil_usuario = toba::usuario()->get_perfiles_funcionales();
    $nombre_completo = toba::usuario()->get_nombre();

    $contenido_exclusivo = '';

    if (in_array("docente", $perfil_usuario) || in_array("docentefadel", $perfil_usuario)) {
        $contenido_exclusivo .= '<p>Pod&eacute;s acceder al instructivo de carga para docentes en <a href="https://drive.google.com/file/d/1VtaOj1DK8GRMU83HbEzbkVerjmTBiwL5/view?usp=sharing" target="_blank">este enlace</a>.</p>';
    }
    if (in_array("sacfale", $perfil_usuario) || in_array("sacrub", $perfil_usuario)) {
        $contenido_exclusivo .= '<p>Pod&eacute;s acceder al instructivo <a href="https://example.com/docentes" target="_blank">este enlace</a>.</p>';
    }
    
    // Definir el arreglo de perfiles válidos
    $perfiles_validos = array(
        'biologiageneral',
        'botanica',
        'didactica',
        'ecologia',
        'educacionfisica',
        'enfermeria',
        'estadistica',
        'explotacionderecursosacuaticos',
        'fisica',
        'geologiaypetroleo',
        'idiomasextranjerosconpropositosespecificos',
        'ingenieriacivil',
        'matematica',
        'politicaeducacional',
        'psicologia',
        'quimica',
        'zoologia'
    );

    // Convertir los perfiles del usuario a minúsculas para una comparación correcta
    $perfil_usuario_lower = array_map('strtolower', $perfil_usuario);
    
    // Verificar si existe alguna coincidencia entre los perfiles del usuario y los perfiles válidos
    if (count(array_intersect($perfil_usuario_lower, $perfiles_validos)) > 0) {
           $contenido_exclusivo .= '<p>En el item DEPARTAMENTOS podr&aacute; revisar los programas.<br><br>   
    Puede cambiar el "Estado" a "Vuelve al Docente para revisar" o "Firmar y enviar a SAC".<br><br>
    Puede agregar comentarios utilizando el campo "Nuevo Comentario".<br><br>
    Recuerde que siempre debe usar la opci&oacute;n "Guardar" para no perder los cambios.</p>';
}

    echo '<div class="card">';
        echo '<h2>Hola ' . $nombre_completo . '</h2>';
        if (!empty($contenido_exclusivo)) {
            echo $contenido_exclusivo;
        }
    echo '</div>';

echo '</div>';





?>
