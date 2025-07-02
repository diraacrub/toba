<?php

// Asegúrate de que el proyecto está definido
if (!defined('apex_pa_proyecto')) {
    define('apex_pa_proyecto', 'personal'); // Usando 'personal' como en tu repo
}

class poblar_usuarios_comando extends toba_aplicacion_comando_base
{
    /**
     * Comando para poblar el DNI de los usuarios y crear nuevos si es necesario.
     * Lee un CSV con las columnas: usuario, dni, nombre, email
     */
    public function opcion__poblar_dni($parametros = [])
    {
        // Ruta al nuevo archivo CSV con DNI
        $csv_path = __DIR__ . '/data/resp.csv'; // Asegúrate de que el archivo se llame así

        if (!file_exists($csv_path)) {
            $this->consola->error("Error: No se encontró el archivo '$csv_path'.");
            return;
        }

        $this->consola->info("--- Iniciando proceso de poblado de DNI y creación de usuarios ---");

        if (($handle = fopen($csv_path, "r")) !== false) {
            // Leer la primera fila (encabezado)
            $header = fgetcsv($handle);
            if ($header === false) {
                $this->consola->error("El archivo CSV está vacío o corrupto.");
                fclose($handle);
                return;
            }

            while (($data = fgetcsv($handle)) !== false) {
                // Mapear encabezado con datos
                $usuario_data = array_combine($header, $data);

                // Datos del CSV
                $usuario_id = trim($usuario_data['usuario']);
                $dni        = trim($usuario_data['dni']);
                $nombre     = trim($usuario_data['nombre']);
                $email      = trim($usuario_data['email']);

                if (empty($usuario_id) || empty($dni)) {
                    $this->consola->advertencia("Saltando fila por datos incompletos (usuario o dni vacíos).");
                    continue;
                }

                if (toba_usuario::existe_usuario($usuario_id)) {
                    // --- Caso 1: El usuario existe, lo actualizamos ---
                    try {
                        $this->actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email);
                        $this->consola->info("OK: Usuario '$usuario_id' actualizado con DNI '$dni'.");
                    } catch (Exception $e) {
                        $this->consola->error("ERROR al actualizar usuario '$usuario_id': " . $e->getMessage());
                    }
                } else {
                    // --- Caso 2: El usuario no existe, lo creamos ---
                    try {
                        // Usamos el DNI como clave inicial. El usuario deberá cambiarla.
                        $this->crear_usuario_con_dni($usuario_id, $dni, $nombre, $email);
                        $this->consola->info("OK: Usuario '$usuario_id' CREADO con DNI '$dni'.");
                    } catch (Exception $e) {
                        $this->consola->error("ERROR al crear usuario '$usuario_id': " . $e->getMessage());
                    }
                }
            }
            fclose($handle);
        } else {
            $this->consola->error("Error: No se pudo abrir el archivo CSV.");
        }
        $this->consola->info("--- Proceso finalizado ---");
    }

    /**
     * Actualiza un usuario existente con su DNI, nombre y email.
     * NO toca la contraseña.
     */
    private function actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email)
    {
        $db = toba::db('toba_3_4');
        $sql = "UPDATE desarrollo.apex_usuario
                SET ciu = " . $db->quote($dni) . ",
                    nombre = " . $db->quote($nombre) . ",
                    email = " . $db->quote($email) . "
                WHERE usuario = " . $db->quote($usuario_id);
        
        $db->ejecutar($sql);
    }

    /**
     * Crea un usuario completamente nuevo, incluyendo su DNI.
     */
    private function crear_usuario_con_dni($usuario_id, $dni, $nombre, $email)
    {
        // 1. Crear el usuario con la API de Toba. Usamos el DNI como clave inicial.
        toba::instancia()->agregar_usuario($usuario_id, $nombre, $dni, ['email' => $email]);

        // 2. Forzar el cambio de clave en el próximo login
        toba_usuario::forzar_cambio_clave($usuario_id);

        // 3. Poblar la columna del DNI (ciu) con una actualización directa.
        $this->actualizar_usuario_con_dni($usuario_id, $dni, $nombre, $email);

        // 4. Asignar el perfil funcional (ajusta 'docente' si es necesario)
        $proyecto = apex_pa_proyecto;
        try {
            toba::instancia()->vincular_usuario($proyecto, $usuario_id, 'docente');
        } catch (Exception $e) {
            // Si ya está vinculado o hay otro error, se registrará en el log principal
            if (stripos($e->getMessage(), 'duplicate key value') === false) {
                throw $e;
            }
        }
    }
}
