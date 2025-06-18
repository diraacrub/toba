<?php
/**
 * Modelo simplificado para operaciones con materias
 */
class modelo_materia {
    // Campos que permiten búsqueda (incluimos todos los campos del JSON)
    static $campos_busqueda = array(
        'id_materia', 'nombre_carrera', 'nombre_materia', 'ano_plan', 'periodo_plan',
        'horas_totales', 'horas_semanales', 'depto_principal', 'depto', 'area',
        'orientacion', 'contenidos_minimos', 'correlativas_para_cursar',
        'correlativas_para_aprobar', 'competencias', 'optativa', 'trayecto',
        'cod_carrera', 'plan_guarani', 'version_guarani', 'plan_mocovi',
        'plan_ordenanzas', 'cod_guarani', 'observaciones'
    );

    // Define campos que contienen texto con posibles acentos
    static $campos_texto = array(
        'nombre_carrera', 'nombre_materia', 'depto_principal', 'depto', 'area',
        'orientacion', 'contenidos_minimos', 'correlativas_para_cursar',
        'correlativas_para_aprobar', 'competencias', 'trayecto', 'observaciones'
    );

    /**
     * Función para traer todas las materias
     */
    static function get_all_materias() {
        $sql = "SELECT * FROM huayca.materias";
        $materias = toba::db()->consultar($sql);

        // Debug: imprimir información sobre la primera materia
        if (!empty($materias)) {
            error_log("[DEBUG] Total de materias obtenidas: " . count($materias));
            error_log("[DEBUG] Primera materia: " . json_encode($materias[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            error_log("[DEBUG] Tipo de valor del campo depto: " . gettype($materias[0]['depto']));
            error_log("[DEBUG] Bytes hexadecimales del depto: " . bin2hex($materias[0]['depto']));
            error_log("[DEBUG] Encoding del depto: " . mb_detect_encoding($materias[0]['depto'], 'UTF-8, ISO-8859-1'));
        } else {
            error_log("[DEBUG] No se encontraron materias");
        }

        return $materias;
    }

    /**
     * Función para traer una materia por su ID
     */
    static function get_materia_by_id($id_materia) {
        $sql = "SELECT * FROM huayca.materias WHERE id_materia = " . quote($id_materia);
        return toba::db()->consultar_fila($sql);
    }

    /**
     * Convierte un valor de UTF-8 a ISO-8859-1 para consultas en BD
     */
    private static function utf8_to_latin1($valor) {
        if (is_string($valor) && mb_detect_encoding($valor, 'UTF-8', true)) {
            return utf8_decode($valor);
        }
        return $valor;
    }

    /**
     * Provides character maps for accent normalization, ensuring maps are in Latin-1.
     * Assumes the PHP source file is UTF-8 encoded.
     */
    private static function get_accent_translation_maps_latin1() {
        // Define accented characters (uppercase) and their unaccented equivalents in UTF-8
        $utf8_accented_upper = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜ';
        $utf8_unaccented_upper = 'AAAAAACEEEEIIIINOOOOOUUUU'; // Corresponding unaccented uppercase characters

        // Convert both map strings to ISO-8859-1 (Latin-1)
        // utf8_decode converts a UTF-8 string to ISO-8859-1.
        // For $utf8_unaccented_upper (pure ASCII), its ISO-8859-1 representation has the same byte values.
        $latin1_accented_upper = utf8_decode($utf8_accented_upper);
        $latin1_unaccented_upper = utf8_decode($utf8_unaccented_upper);

        return [$latin1_accented_upper, $latin1_unaccented_upper];
    }

    /**
     * Wraps an SQL string expression (column name or value) with TRANSLATE for accent insensitivity.
     * $sql_expr is an SQL expression string, e.g., "UPPER(nombre_materia)" or "UPPER('SOME_VALUE')".
     * The maps used for TRANSLATE are ISO-8859-1 encoded.
     */
    private static function normalize_sql_expr_for_accent_insensitivity($sql_expr) {
        list($map_from_latin1, $map_to_latin1) = self::get_accent_translation_maps_latin1();

        // Quote the map strings for use as string literals in the SQL query.
        // Toba's quote function will escape them appropriately.
        // The content of these quoted strings will be the Latin-1 byte sequences.
        $quoted_map_from = toba::db()->quote($map_from_latin1);
        $quoted_map_to = toba::db()->quote($map_to_latin1);

        return "TRANSLATE($sql_expr, $quoted_map_from, $quoted_map_to)";
    }

      /**
     * Buscar materias con múltiples filtros combinables
     * Búsqueda case-insensitive y accent-insensitive
     * Permite usar _exact al final de un parámetro para buscar coincidencia exacta
     */
    static function buscar_materias($filtros = array()) {
        $where = array();
        $params = array();

        error_log("[DEBUG] Filtros recibidos: " . json_encode($filtros, JSON_UNESCAPED_UNICODE));

        // Convertir filtros de UTF-8 a ISO-8859-1 para coincidir con la BD
        foreach ($filtros as $campo => $valor) {
            // Verificar si es una búsqueda exacta (campo termina con _exact)
            $busqueda_exacta = false;
            if (substr($campo, -6) === '_exact') {
                $campo_real = substr($campo, 0, -6);
                $busqueda_exacta = true;
            } else {
                $campo_real = $campo;
            }
              if (in_array($campo_real, self::$campos_busqueda)) {
                $valor_latin1 = self::utf8_to_latin1($valor);

                // Campos que siempre deben usar coincidencia exacta (pero case-insensitive)
                $campos_exactos = array('depto', 'area', 'orientacion');

                if (in_array($campo_real, self::$campos_texto)) {
                    // Apply UPPER to the column name part of the expression
                    $column_expr_upper = "UPPER($campo_real)";
                    // Normalize the column expression for accent insensitivity
                    $normalized_column_expr = self::normalize_sql_expr_for_accent_insensitivity($column_expr_upper);

                    // Si es un campo de coincidencia exacta o se especificó _exact
                    if ($busqueda_exacta || in_array($campo_real, $campos_exactos)) {
                        // Búsqueda exacta, case-insensitive, and accent-insensitive
                        $value_expr_upper = "UPPER(" . toba::db()->quote($valor_latin1) . ")";
                        $normalized_value_expr = self::normalize_sql_expr_for_accent_insensitivity($value_expr_upper);

                        $where[] = "$normalized_column_expr = $normalized_value_expr";
                        error_log("[DEBUG] Filtro EXACTO ACCENT-INSENSITIVE para campo '$campo_real': $normalized_column_expr = $normalized_value_expr");
                    } else {
                        // Búsqueda parcial, case-insensitive, and accent-insensitive
                        // For LIKE, the pattern needs to be normalized.
                        $value_expr_upper_like = "UPPER(" . toba::db()->quote('%' . $valor_latin1 . '%') . ")";
                        $normalized_value_expr_like = self::normalize_sql_expr_for_accent_insensitivity($value_expr_upper_like);

                        $where[] = "$normalized_column_expr LIKE $normalized_value_expr_like";
                        error_log("[DEBUG] Filtro PARCIAL ACCENT-INSENSITIVE para campo '$campo_real': $normalized_column_expr LIKE $normalized_value_expr_like");
                    }
                } else {
                    // Para campos numéricos o códigos, siempre usamos igualdad exacta
                    $valor_sql = toba::db()->quote($valor_latin1); // Already Latin-1
                    $where[] = "$campo_real = $valor_sql";
                    error_log("[DEBUG] Filtro para campo '$campo_real': $campo_real = $valor_sql");
                }
            }
        }

        try {
            // Construir la consulta SQL
            $sql = "SELECT * FROM huayca.materias";

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            error_log("[DEBUG] SQL generado: $sql");

            // Ejecutar la consulta
            $resultado = toba::db()->consultar($sql);
            error_log("[DEBUG] Total de resultados: " . count($resultado));

            return $resultado;

        } catch (Exception $e) {
            error_log("[ERROR] Error en consulta SQL: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            return array();
        }
    }

    /**
     * Función para traer materias por departamento (mantenida por compatibilidad)
     */
    static function get_materias_by_depto($depto) {
        return self::buscar_materias(array('depto' => $depto));
    }
}
