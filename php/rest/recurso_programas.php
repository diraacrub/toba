<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;

// Se incluye el modelo. La ruta es relativa: retrocede un directorio y entra a "modelos"
require_once __DIR__ . '/../modelos/modelo_programa.php';

/**
 * @description Operaciones sobre Programas
 */
class recurso_programas implements modelable
{
    // Método documentativo para la generación de modelos (no obligatorio para el funcionamiento)
    static function _get_modelos(){
        return array();
    }

    // Método para obtener la lista de programas.
    // Si se pasa el parámetro "ids" en la URL, filtra por esos IDs; de lo contrario, devuelve todos.
    static function get_list(){
        // Se obtiene el parámetro 'ids' de la query string usando $_GET
        $ids_param = isset($_GET['ids']) ? $_GET['ids'] : null;
        if($ids_param){
            $ids = explode(',', $ids_param);
            $programas = modelo_programa::get_programas_por_ids($ids);
        } else {
            $programas = modelo_programa::get_programas_con_materia();
        }
        rest::response()->get($programas);
    }

    // Método para obtener un programa en particular junto con su materia.
    // Se invoca, por ejemplo, con GET /rest/programas/{id}
    static function get($id){
        $programa = modelo_programa::get_programa_con_materia($id);
        rest::response()->get($programa);
    }
}
