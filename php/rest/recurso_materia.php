<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;

// Se incluye el modelo. La ruta es relativa: retrocede un directorio y entra a "modelos"
require_once __DIR__ . '/../modelos/modelo_materia.php';

/**
 * @description Operaciones sobre Materias
 */
class recurso_materias implements modelable
{
    // Método documentativo para la generación de modelos (no obligatorio para el funcionamiento)
    static function _get_modelos(){
        return array();
    }    // Método para obtener la lista de materias.
    // Permite filtrar por:
    // - id_materia (parámetro "ids")
    // - departamento (búsqueda en depto_principal y depto)
    // - depto_principal (búsqueda específica en depto_principal)
    // - depto (búsqueda específica en depto)
    // - area (búsqueda en área)
    // - orientacion (búsqueda en orientación)
    // - contenidos_minimos_empty (true/false para verificar si contenidos_minimos está vacío o no)
    // - contenidos_minimos (búsqueda flexible en contenidos_minimos, insensible a mayúsculas, espacios, tildes)
    // - nombre_materia (búsqueda con LIKE, insensible a mayúsculas, espacios, tildes)
    // - nombre_carrera (búsqueda con LIKE)
    // - ano_plan (valor exacto)
    // - periodo_plan (valor exacto)
    // - horas_totales (valor exacto)
    // - horas_semanales (valor exacto)
    // - optativa (valor exacto)
    // - trayecto (valor exacto)
    // - cod_carrera (valor exacto)
    // - plan_guarani (valor exacto)
    // - cod_guarani (búsqueda exacta)
    static function get_list(){
        // Parámetros de filtrado
        $ids_param = isset($_GET['ids']) ? $_GET['ids'] : null;
        
        // Filtros específicos para materias
        $departamento = isset($_GET['departamento']) ? $_GET['departamento'] : null;
        $depto_principal = isset($_GET['depto_principal']) ? $_GET['depto_principal'] : null;
        $depto = isset($_GET['depto']) ? $_GET['depto'] : null;
        $area = isset($_GET['area']) ? $_GET['area'] : null;
        $orientacion = isset($_GET['orientacion']) ? $_GET['orientacion'] : null;
        $contenidos_minimos_empty = isset($_GET['contenidos_minimos_empty']) ? $_GET['contenidos_minimos_empty'] : null;
        $contenidos_minimos = isset($_GET['contenidos_minimos']) ? $_GET['contenidos_minimos'] : null;
        $nombre_materia = isset($_GET['nombre_materia']) ? $_GET['nombre_materia'] : null;
        $nombre_carrera = isset($_GET['nombre_carrera']) ? $_GET['nombre_carrera'] : null;
        $ano_plan = isset($_GET['ano_plan']) ? $_GET['ano_plan'] : null;
        $periodo_plan = isset($_GET['periodo_plan']) ? $_GET['periodo_plan'] : null;
        $horas_totales = isset($_GET['horas_totales']) ? $_GET['horas_totales'] : null;
        $horas_semanales = isset($_GET['horas_semanales']) ? $_GET['horas_semanales'] : null;
        $optativa = isset($_GET['optativa']) ? $_GET['optativa'] : null;
        $trayecto = isset($_GET['trayecto']) ? $_GET['trayecto'] : null;
        $cod_carrera = isset($_GET['cod_carrera']) ? $_GET['cod_carrera'] : null;
        $plan_guarani = isset($_GET['plan_guarani']) ? $_GET['plan_guarani'] : null;
        $cod_guarani = isset($_GET['cod_guarani']) ? $_GET['cod_guarani'] : null;

        // Armar arreglo de filtros (solo los que tienen valor)
        $filters = array();
        if ($departamento)           $filters['departamento'] = $departamento;
        if ($depto_principal)        $filters['depto_principal'] = $depto_principal;
        if ($depto)                  $filters['depto'] = $depto;
        if ($area)                   $filters['area'] = $area;
        if ($orientacion)            $filters['orientacion'] = $orientacion;
        if ($contenidos_minimos_empty !== null) $filters['contenidos_minimos_empty'] = $contenidos_minimos_empty;
        if ($contenidos_minimos)     $filters['contenidos_minimos'] = $contenidos_minimos;
        if ($nombre_materia)         $filters['nombre_materia'] = $nombre_materia;
        if ($nombre_carrera)         $filters['nombre_carrera'] = $nombre_carrera;
        if ($ano_plan)               $filters['ano_plan'] = $ano_plan;
        if ($periodo_plan)           $filters['periodo_plan'] = $periodo_plan;
        if ($horas_totales)          $filters['horas_totales'] = $horas_totales;
        if ($horas_semanales)        $filters['horas_semanales'] = $horas_semanales;
        if ($optativa)               $filters['optativa'] = $optativa;
        if ($trayecto)               $filters['trayecto'] = $trayecto;
        if ($cod_carrera)            $filters['cod_carrera'] = $cod_carrera;
        if ($plan_guarani)           $filters['plan_guarani'] = $plan_guarani;
        if ($cod_guarani)            $filters['cod_guarani'] = $cod_guarani;

        // Si hay filtros o IDs específicos, usamos el método de filtrado
        if(!empty($filters) || $ids_param){
            if($ids_param){
                $ids = explode(',', $ids_param);
                $materias = modelo_materia::get_materias_by_filters($filters, $ids);
            } else {
                $materias = modelo_materia::get_materias_by_filters($filters);
            }
        } else {
            // Sin filtros adicionales, obtenemos todas las materias
            $materias = modelo_materia::get_all_materias();
        }

        rest::response()->get($materias);
    }

    // Método para obtener una materia en particular por su ID
    // Se invoca, por ejemplo, con GET /rest/materias/{id}
    static function get($id){
        $materia = modelo_materia::get_materia_by_id($id);
        rest::response()->get($materia);
    }
}
