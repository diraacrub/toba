<?php

require_once 'comandos/cargar_usuarios_masivos.php'; // Incluir el script del comando

/**
 * Clase para registrar comandos personalizados de Huayca.
 */
class huayca_comando implements toba_aplicacion_comando
{
    private $modelo;

    /**
     * Establece el entorno del comando.
     */
    public function set_entorno($manejador_interface, toba_aplicacion_modelo $modelo)
    {
        $this->modelo = $modelo;
    }

    /**
     * Opciones de instalación (existente).
     */
    public function opcion__instalar($parametros)
    {
        echo "Ejecutando instalación del proyecto huayca...\n";
        $this->modelo->instalar($parametros);
    }

    /**
     * Opciones de migración (existente).
     */
    public function opcion__migrar($parametros)
    {
        echo "Ejecutando migración de huayca...\n";
        $this->modelo->migrar($parametros['desde'], $parametros['hasta']);
    }

    /**
     * Registrar el nuevo comando de carga masiva de usuarios.
     */
    public function opcion__cargar_usuarios_masivos($parametros)
    {
        $cargar_usuarios = new cargar_usuarios_masivos();
        $cargar_usuarios->ejecutar();
    }
}
