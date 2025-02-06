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
        $csv_path = __DIR__ . '/comandos/data/u_docentes.csv';

        if (!file_exists($csv_path)) {
            echo "Error: No se encontró el archivo $csv_path\n";
            return;
        }

        // Abrir el archivo CSV
        if (($handle = fopen($csv_path, "r")) !== false) {
            $header = fgetcsv($handle); // Leer la primera fila como encabezado
            echo "Procesando usuarios...\n";

            while (($data = fgetcsv($handle)) !== false) {
                $usuario_data = array_combine($header, $data); // Mapear encabezado con datos

                try {
                    $this->crear_usuario(
                        $usuario_data['usuario'],
                        $usuario_data['clave'],
                        $usuario_data['nombre'],
                        $usuario_data['email']
                    );
                    echo "Usuario '{$usuario_data['usuario']}' creado con éxito.\n";
                } catch (Exception $e) {
                    echo "Error al crear el usuario '{$usuario_data['usuario']}': {$e->getMessage()}\n";
                }
            }

            fclose($handle);
        } else {
            echo "Error: No se pudo abrir el archivo CSV.\n";
        }
    }

    private function crear_usuario($usuario, $clave, $nombre, $email)
    {
        // Verificar si el usuario ya existe
        if (toba_usuario::existe_usuario($usuario)) {
            throw new Exception("El usuario ya existe.");
        }

        // Crear el usuario
        toba::instancia()->agregar_usuario($usuario, $nombre, $clave, ['email' => $email]);

        // Forzar cambio de contraseña en el próximo inicio de sesión
        toba_usuario::forzar_cambio_clave($usuario);

        // Asignar el perfil funcional 'docente'
        $proyecto = apex_pa_proyecto; // Usar el proyecto definido
        toba::instancia()->vincular_usuario($proyecto, $usuario, 'docente');
    }
}
