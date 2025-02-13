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
            text-align: center;
        }
        .card h2 {
            color: #001f3f;
            font-size: 20px;
            margin-top: 0;
        }
        .card p {
            color: #001f3f;
            font-size: 16px;
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

        if (in_array("docente", $perfil_usuario) || in_array("docente fale", $perfil_usuario)) {
            $contenido_exclusivo .= '<p>Pod&eacute;s acceder al instructivo de carga en <a href="https://example.com/docentes" target="_blank">este enlace</a>.</p>';
        }
        if (in_array("sacfale", $perfil_usuario) || in_array("sacrub", $perfil_usuario)) {
            $contenido_exclusivo .= '<p>Pod&eacute;s acceder al instructivo de carga en <a href="https://example.com/docentes" target="_blank">este enlac</a>.</p>';
      }
        if (in_array("ecologia", $perfil_usuario) || in_array("zoologia", $perfil_usuario) || in_array("matematica", $perfil_usuario)) {
            $contenido_exclusivo .= '<p>Pod&eacute;s acceder al instructivo de carga en <a href="https://example.com/docentes" target="_blank">este enlac</a>.</p>';
        }

        echo '<div class="card">';
            echo '<h2>' . $nombre_completo . '</h2>';
            if (!empty($contenido_exclusivo)) {
                echo $contenido_exclusivo;
            }
        echo '</div>';

    echo '</div>';
?>
