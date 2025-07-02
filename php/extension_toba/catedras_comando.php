<?php

// Definir el proyecto actual
if (!defined('apex_pa_proyecto')) {
    define('apex_pa_proyecto', 'catedras');
}

class catedras_comando extends toba_aplicacion_comando_base
{
    //=======================================================================================
    // --- ACCIÓN ORIGINAL: Carga masiva sin DNI ---
    //=======================================================================================

    public function opcion__cargar_usuarios_docentes($parametros = [])
    {
        $csv_path = __DIR__ . '/comandos/data/u_responsables.csv';
        if (!file_exists($csv_path)) {
            echo "Error: No se encontró el archivo '$csv_path'\n";
            return;
        }

        if (($handle = fopen($csv_path, "r")) !== false) {
            $header = fgetcsv($handle);
            echo "--- Procesando carga original de usuarios (desde u_responsables.csv) ---\n";
            while (($data = fgetcsv($handle)) !== false) {
                $usuario_data = array_combine($header, $data);
                $usuario = $this->convertToLatin1Auto(trim($usuario_data['usuario']));
                $clave   = $this->convertToLatin1Auto(trim($usuario_data['clave']));
                $nombre  = $this->convertToLatin1Auto(trim($usuario_data['nombre']));
                $email   = $this->convertToLatin1Auto(trim($usuario_data['email']));
                if (toba_usuario::existe_usuario($usuario)) {
                    try {
                        $this->actualizar_usuario_sin_modificar_clave($usuario, $nombre, $email);
                        echo "OK: Usuario '{$usuario}' actualizado (sin modificar clave).\n";
                    } catch (Exception $e) {
                        echo "ERROR al actualizar el usuario '{$usuario}': {$e->getMessage()}\n";
                    }
                } else {
                    try {
                        $this->crear_usuario($usuario, $clave, $nombre, $email);
                        echo "OK: Usuario '{$usuario}' CREADO.\n";
                    } catch (Exception $e) {
                        echo "ERROR al crear el usuario '{$usuario}': {$e->getMessage()}\n";
                    }
                }
            }
            fclose($handle);
        } else {
            echo "Error: No se pudo abrir el archivo CSV.\n";
        }
        echo "--- Proceso de carga original finalizado ---\n";
    }

    //=======================================================================================
    // --- NUEVA ACCIÓN: Poblar DNI y crear/actualizar usuarios ---
    //=======================================================================================

    public function opcion__poblar_dni($parametros = [])
    {
        $csv_path = __DIR__ . '/comandos/data/resp.csv';
        if (!file_exists($csv_path)) {
            echo "Error: No se encontró el archivo '$csv_path'.\n";
            return;
        }
        echo "--- Iniciando proceso de poblado de DNI y creación/actualización de usuarios ---\n";
        if (($handle = fopen($csv_path, "r")) !== false) {
            $header = fgetcsv($handle);
            if ($header === false) {
                echo "Error: El archivo CSV está vacío o corrupto.\n";
                fclose($handle);
                return;
            }
            while (($data = fgetcsv($handle)) !== false) {
                $usuario_data = array_combine($header, $data);
                $usuario_id = trim($usuario_data['usuario']);
                $dni        = trim($usuario_data['dni']);
                $nombre     = trim($usuario_data['nombre']);
                $email      = trim($usuario_data['email']);
                if (empty($usuario_id) || empty($dni)) {
                    echo "ADVERTENCIA: Saltando fila por datos incompletos (usuario o dni vacíos).\n";
                    continue;
                }
                if (toba_usuario::existe_usuario($usuario_id)) {
                    try {
                        $this->actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email);
                        echo "OK: Usuario '$usuario_id' ACTUALIZADO con DNI '$dni'.\n";
                    } catch (Exception $e) {
                        echo "ERROR al actualizar usuario '$usuario_id': " . $e->getMessage() . "\n";
                    }
                } else {
                    try {
                        $this->crear_usuario_con_dni($usuario_id, $dni, $nombre, $email);
                        echo "OK: Usuario '$usuario_id' CREADO con DNI '$dni'.\n";
                    } catch (Exception $e) {
                        echo "ERROR al crear usuario '$usuario_id': " . $e->getMessage() . "\n";
                    }
                }
            }
            fclose($handle);
        } else {
            echo "Error: No se pudo abrir el archivo CSV.\n";
        }
        echo "--- Proceso finalizado ---\n";
    }

    //=======================================================================================
    // --- MÉTODOS PRIVADOS (Helpers) ---
    //=======================================================================================

    private function convertToLatin1Auto($value) {
        if (!is_string($value) || $value === '') { return $value; }
        $detected = mb_detect_encoding($value, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true);
        if ($detected && $detected !== 'ISO-8859-1' && $detected !== 'WINDOWS-1252') {
            $value = mb_convert_encoding($value, 'ISO-8859-1', $detected);
        }
        return $value;
    }

    private function crear_usuario($usuario, $clave, $nombre, $email) {
        if (toba_usuario::existe_usuario($usuario)) { throw new Exception("El usuario '{$usuario}' ya existe."); }
        toba::instancia()->agregar_usuario($usuario, $nombre, $clave, ['email' => $email]);
        toba_usuario::forzar_cambio_clave($usuario);
        $this->vincular_perfil_docente($usuario);
    }

    private function actualizar_usuario_sin_modificar_clave($usuario, $nombre, $email) {
        $db = toba::db();
        $sql = "UPDATE desarrollo.apex_usuario SET nombre = " . $db->quote($nombre) . ", email  = " . $db->quote($email) . " WHERE usuario = " . $db->quote($usuario);
        $db->ejecutar($sql);
        $this->vincular_perfil_docente($usuario);
    }
    
    private function actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email) {
        // --- LÍNEA CORREGIDA ---
        $db = toba::db(); // Usa la fuente de datos por defecto, como en el resto del script
        $sql = "UPDATE desarrollo.apex_usuario SET ciu = " . $db->quote($dni) . ", nombre = " . $db->quote($nombre) . ", email = " . $db->quote($email) . " WHERE usuario = " . $db->quote($usuario_id);
        $db->ejecutar($sql);
    }

    private function crear_usuario_con_dni($usuario_id, $dni, $nombre, $email) {
        toba::instancia()->agregar_usuario($usuario_id, $nombre, $dni, ['email' => $email]);
        toba_usuario::forzar_cambio_clave($usuario_id);
        $this->actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email);
        $this->vincular_perfil_docente($usuario_id);
    }

    private function vincular_perfil_docente($usuario) {
        $proyecto = apex_pa_proyecto;
        try {
            toba::instancia()->vincular_usuario($proyecto, $usuario, 'docente');
        } catch (Exception $e) {
            if (stripos($e->getMessage(), 'duplicate key value') === false && stripos($e->getMessage(), 'ya existe la clave') === false) {
                throw $e;
            }
        }
    }
}
