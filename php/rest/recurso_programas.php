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
    // Permite filtrar por:
    // - IDs (parámetro "ids")
    // - ano_academico (tabla programas)
    // - cod_carrera (tabla materias)
    // - nombre_materia (tabla materias, búsqueda con LIKE)
    // - horas_semanales (tabla materias)
    // - depto_principal (tabla materias)
    // - depto (tabla materias)
    // - area (tabla materias)
    // - orientacion (tabla materias)
    // - contenidos_minimos (búsqueda flexible en tabla materias, insensible a mayúsculas, espacios, tildes, y sin importar el orden)
    // Además, si se pasa "reducido=1" se devuelven solo los campos reducidos.
    static function get_list(){
        // Parámetros básicos de filtrado
        $ids_param          = isset($_GET['ids']) ? $_GET['ids'] : null;
        $reducido           = isset($_GET['reducido']) ? $_GET['reducido'] : false;
        $ano_academico      = isset($_GET['ano_academico']) ? $_GET['ano_academico'] : null;
        $cod_carrera        = isset($_GET['cod_carrera']) ? $_GET['cod_carrera'] : null;
        $nombre_materia     = isset($_GET['nombre_materia']) ? $_GET['nombre_materia'] : null;
        
        // Nuevos filtros
        $horas_semanales    = isset($_GET['horas_semanales']) ? $_GET['horas_semanales'] : null;
        $depto_principal    = isset($_GET['depto_principal']) ? $_GET['depto_principal'] : null;
        $depto              = isset($_GET['depto']) ? $_GET['depto'] : null;
        $area               = isset($_GET['area']) ? $_GET['area'] : null;
        $orientacion        = isset($_GET['orientacion']) ? $_GET['orientacion'] : null;
        $contenidos_minimos = isset($_GET['contenidos_minimos']) ? $_GET['contenidos_minimos'] : null;

        // Armar arreglo de filtros (solo los que tienen valor)
        $filters = array();
        if ($ano_academico)      $filters['ano_academico']      = $ano_academico;
        if ($cod_carrera)        $filters['cod_carrera']        = $cod_carrera;
        if ($nombre_materia)     $filters['nombre_materia']     = $nombre_materia;
        if ($horas_semanales)     $filters['horas_semanales']    = $horas_semanales;
        if ($depto_principal)     $filters['depto_principal']    = $depto_principal;
        if ($depto)               $filters['depto']              = $depto;
        if ($area)                $filters['area']               = $area;
        if ($orientacion)         $filters['orientacion']        = $orientacion;
        if ($contenidos_minimos)  $filters['contenidos_minimos'] = $contenidos_minimos;

        // Si hay al menos un filtro, se invoca el método que arma la cláusula WHERE dinámicamente.
        if(!empty($filters)){
            if($reducido){
                if($ids_param){
                    $ids = explode(',', $ids_param);
                    $programas = modelo_programa::get_programas_reducidos_by_filters($filters, $ids);
                } else {
                    $programas = modelo_programa::get_programas_reducidos_by_filters($filters);
                }
            } else {
                if($ids_param){
                    $ids = explode(',', $ids_param);
                    $programas = modelo_programa::get_programas_con_materia_by_filters($filters, $ids);
                } else {
                    $programas = modelo_programa::get_programas_con_materia_by_filters($filters);
                }
            }
        } else {
            // Sin filtros adicionales, se mantiene la lógica existente.
            if ($reducido) {
                if ($ids_param) {
                    $ids = explode(',', $ids_param);
                    $programas = modelo_programa::get_programas_reducidos_por_ids($ids);
                } else {
                    $programas = modelo_programa::get_programas_reducidos();
                }
            } else {
                if ($ids_param) {
                    $ids = explode(',', $ids_param);
                    $programas = modelo_programa::get_programas_por_ids($ids);
                } else {
                    $programas = modelo_programa::get_programas_con_materia();
                }
            }
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
