<?php
use SIUToba\rest\rest;
use SIUToba\rest\lib\modelable;

require_once __DIR__ . '../../modelos/modelo_materia.php';

/**
 * @description Operaciones sobre Materias
 * 
 * Permite búsquedas combinadas por cualquier campo, todas son case y accent insensitive:
 *  - Campos generales de texto (búsqueda parcial): ?campo=valor
 *    Ejemplo: materias?nombre_materia=biolog&contenidos_minimos=evolucion
 *    Nota: "biolog" encontrará "BIOLÓGICAS", "Biología", "biologias", etc.
 *    Nota: "fisica" encontrará "FÍSICA", "Física", "fisica", etc.
 * 
 *  - Campos depto, area y orientacion (búsqueda exacta pero case/accent-insensitive): ?campo=valor
 *    Ejemplo: materias?depto=fisica&orientacion=teorica
 *    Estos campos usan coincidencia exacta pero ignorando mayúsculas/minúsculas y acentos
 *    Por ejemplo, "fisica" encontrará "FÍSICA" pero no "FISICA TEORICA"
 * 
 *  - Para forzar búsquedas exactas en cualquier campo: ?campo_exact=valor
 *    Ejemplo: materias?nombre_materia_exact=biologia general
 *    Nota: todas las búsquedas son case y accent insensitive incluso con _exact
 * 
 * Todos los campos son buscables: id_materia, nombre_carrera, nombre_materia,
 * ano_plan, periodo_plan, horas_totales, horas_semanales, depto_principal,
 * depto, area, orientacion, etc.
 */
class recurso_materias implements modelable
{
    // Método documentativo para la generación de modelos
    static function _get_modelos()
    {
        return array();
    }    /**
     * Obtiene la lista de materias.
     * Permite filtrar por cualquier campo combinable, con búsqueda case y accent insensitive
     * Soporta búsqueda exacta añadiendo _exact al nombre del parámetro
     */
    static function get_list() 
    {
        // Log request details
        error_log("[DEBUG] Solicitud recibida: " . $_SERVER['REQUEST_URI']);
        error_log("[DEBUG] Query string: " . $_SERVER['QUERY_STRING']);

        // Recolectar todos los parámetros de búsqueda
        $filtros = array();

        // Procesar parámetros normales
        foreach (modelo_materia::$campos_busqueda as $campo) {
            // Parámetro normal (búsqueda parcial)
            if (isset($_GET[$campo])) {
                $filtros[$campo] = urldecode($_GET[$campo]);
                error_log("[DEBUG] Filtro parcial '$campo': '" . $filtros[$campo] . "'");
            }

            // Parámetro con _exact (búsqueda exacta)
            $param_exact = $campo . '_exact';
            if (isset($_GET[$param_exact])) {
                $filtros[$param_exact] = urldecode($_GET[$param_exact]);
                error_log("[DEBUG] Filtro exacto '$param_exact': '" . $filtros[$param_exact] . "'");
            }
        }

        // Determinar si debemos buscar con filtros o traer todo
        if (!empty($filtros)) {
            error_log("[DEBUG] Buscando con filtros: " . json_encode($filtros, JSON_UNESCAPED_UNICODE));

            // Buscar materias con los filtros proporcionados
            $materias = modelo_materia::buscar_materias($filtros);

            // Verificar resultados
            if (empty($materias)) {
                error_log("[DEBUG] Los filtros no devolvieron resultados");
            } else {
                error_log("[DEBUG] Los filtros devolvieron " . count($materias) . " resultados");
            }
        } else {
            // Sin filtros, obtener todas las materias
            $materias = modelo_materia::get_all_materias();
            error_log("[DEBUG] Consulta sin filtros devolvió " . count($materias) . " materias");
        }

        // Convertir los resultados de ISO-8859-1 a UTF-8 para la respuesta JSON
        foreach ($materias as &$materia) {
            foreach (modelo_materia::$campos_texto as $campo) {
                if (isset($materia[$campo]) && !mb_check_encoding($materia[$campo], 'UTF-8')) {
                    $materia[$campo] = utf8_encode($materia[$campo]);
                }
            }
        }

        // Enviar respuesta
        rest::response()->get($materias, 200, JSON_UNESCAPED_UNICODE);
    }
      /**
     * Obtiene una materia por su ID
     */
    static function get($id) 
    {
        $materia = modelo_materia::get_materia_by_id($id);

        // Convertir campos de texto de ISO-8859-1 a UTF-8
        if ($materia) {
            foreach (modelo_materia::$campos_texto as $campo) {
                if (isset($materia[$campo]) && !mb_check_encoding($materia[$campo], 'UTF-8')) {
                    $materia[$campo] = utf8_encode($materia[$campo]);
                }
            }
        }

        rest::response()->get($materia, 200, JSON_UNESCAPED_UNICODE);
    }
}
