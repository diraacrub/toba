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
                    // Si es un campo de coincidencia exacta o se especificó _exact
                    if ($busqueda_exacta || in_array($campo_real, $campos_exactos)) {
                        // Búsqueda exacta pero case-insensitive para campos de texto
                        $valor_sql = toba::db()->quote($valor_latin1);
                        $where[] = "UPPER($campo_real) = UPPER($valor_sql)";
                        error_log("[DEBUG] Filtro EXACTO para campo '$campo_real': UPPER($campo_real) = UPPER($valor_sql)");
                    } else {
                        // Búsqueda parcial para el resto de campos de texto
                        $valor_sql = toba::db()->quote('%' . $valor_latin1 . '%');
                        $where[] = "UPPER($campo_real) LIKE UPPER($valor_sql)";
                        error_log("[DEBUG] Filtro PARCIAL para campo '$campo_real': UPPER($campo_real) LIKE UPPER($valor_sql)");
                    }
                } else {
                    // Para campos numéricos o códigos, siempre usamos igualdad exacta
                    $valor_sql = toba::db()->quote($valor_latin1);
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
