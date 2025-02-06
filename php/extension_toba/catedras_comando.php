<?php

// Definir el proyecto actual
if (!defined('apex_pa_proyecto')) {
    define('apex_pa_proyecto', 'catedras'); // Cambia 'catedras' si el proyecto tiene otro ID
}

class catedras_comando extends toba_aplicacion_comando_base
{
    public function opcion__cargar_usuarios_docentes($parametros = [])
    {
        // Ruta al archivo CSV
        $csv_path = __DIR__ . '/comandos/data/u_responsables.csv';

        if (!file_exists($csv_path)) {
            echo "Error: No se encontró el archivo $csv_path\n";
            return;
        }

        // Abrir el archivo CSV
        if (($handle = fopen($csv_path, "r")) !== false) {
            // Leer la primera fila (encabezado)
            $header = fgetcsv($handle);
            if ($header !== false) {
                // Detectar y convertir cada campo a LATIN1 para la DB
                $header = array_map([$this, 'convertToLatin1Auto'], $header);
            }

            echo "Procesando usuarios...\n";

            while (($data = fgetcsv($handle)) !== false) {
                // Detectar y convertir cada campo a LATIN1 para la DB
                $data = array_map([$this, 'convertToLatin1Auto'], $data);

                // Mapear encabezado con datos
                $usuario_data = array_combine($header, $data);

                // Asegurate de que en tu CSV las columnas sean: usuario, clave, nombre, email
                $usuario = $usuario_data['usuario'];
                $clave   = $usuario_data['clave'];
                $nombre  = $usuario_data['nombre'];
                $email   = $usuario_data['email'];

                if (toba_usuario::existe_usuario($usuario)) {
                    // Si existe, actualizar (SIN tocar la clave)
                    try {
                        $this->actualizar_usuario_sin_modificar_clave($usuario, $nombre, $email);
                        echo "Usuario '{$usuario}' actualizado con éxito (sin modificar clave).\n";
                    } catch (Exception $e) {
                        echo "Error al actualizar el usuario '{$usuario}': {$e->getMessage()}\n";
                    }
                } else {
                    // Si no existe, crear (incluye clave)
                    try {
                        $this->crear_usuario($usuario, $clave, $nombre, $email);
                        echo "Usuario '{$usuario}' creado con éxito.\n";
                    } catch (Exception $e) {
                        echo "Error al crear el usuario '{$usuario}': {$e->getMessage()}\n";
                    }
                }
            }

            fclose($handle);
        } else {
            echo "Error: No se pudo abrir el archivo CSV.\n";
        }
    }

    /**
     * Detecta si un valor está en UTF-8 o ISO-8859-1/Windows-1252,
     * y si es UTF-8 lo convierte a ISO-8859-1 (Latin-1) para guardarlo en la DB.
     *
     * Si ya es Latin-1, lo deja como está.
     */
    private function convertToLatin1Auto($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        // Detectar si es UTF-8, ISO-8859-1 o Windows-1252
        $detected = mb_detect_encoding($value, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true);

        if ($detected && $detected !== 'ISO-8859-1' && $detected !== 'WINDOWS-1252') {
            // Si se detecta UTF-8, convertirlo a Latin-1
            $value = mb_convert_encoding($value, 'ISO-8859-1', $detected);
        }
        // Si ya está en Latin-1 o Windows-1252, no hacemos nada
        // (Windows-1252 and ISO-8859-1 are almost identical for Spanish/Ñ).
        return $value;
    }

    /**
     * Crea un usuario nuevo en apex_usuario.
     * - Se setea la clave y se fuerza el cambio en el próximo login.
     */
    private function crear_usuario($usuario, $clave, $nombre, $email)
    {
        // Verifica nuevamente si existe (simple sanity check)
        if (toba_usuario::existe_usuario($usuario)) {
            throw new Exception("El usuario '{$usuario}' ya existe.");
        }

        // Crear usuario mediante la API de Toba
        toba::instancia()->agregar_usuario($usuario, $nombre, $clave, ['email' => $email]);

        // Forzar cambio de clave en el próximo login
        toba_usuario::forzar_cambio_clave($usuario);

        // Asignar el perfil funcional 'docente'
        $proyecto = apex_pa_proyecto;
        try {
            toba::instancia()->vincular_usuario($proyecto, $usuario, 'docente');
        } catch (Exception $e) {
            // Si ya está vinculado, ignoramos. Caso contrario, re-lanzar la excepción.
            if (stripos($e->getMessage(), 'duplicate key value') === false) {
                throw $e;
            }
        }
    }

    /**
     * Actualiza el nombre e email de un usuario, sin modificar la clave.
     */
    private function actualizar_usuario_sin_modificar_clave($usuario, $nombre, $email)
    {
        // Conexión a la base de datos
        $db = toba::db();

        // Quotea para evitar inyecciones
        $usuario_quoted = $db->quote($usuario);
        $nombre_quoted  = $db->quote($nombre);
        $email_quoted   = $db->quote($email);

        // Actualizamos SOLO nombre y email. La clave NO se toca.
        $sql = "UPDATE desarrollo.apex_usuario
                SET nombre = $nombre_quoted,
                    email  = $email_quoted
                WHERE usuario = $usuario_quoted";
        $db->ejecutar($sql);

        $proyecto = apex_pa_proyecto;
        try {
            toba::instancia()->vincular_usuario($proyecto, $usuario, 'docente');
        } catch (Exception $e) {
            if (stripos($e->getMessage(), 'duplicate key value') === false) {
                throw $e;
            }
        }
    }
}
