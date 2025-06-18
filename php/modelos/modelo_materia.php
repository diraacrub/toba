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
    }    /**
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
     * Normaliza caracteres con acentos a su versión sin acento
     * Implementación sin usar unaccent de PostgreSQL
     */
    private static function normalizar_acentos($texto) {
        if (!is_string($texto)) {
            return $texto;
        }

        $caracteres_especiales = array(
            // Mayúsculas
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ñ' => 'N',
            // Minúsculas
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ñ' => 'n'
        );

        return strtr($texto, $caracteres_especiales);
    }
    
    /**
     * Genera una expresión SQL para normalizar acentos en PostgreSQL
     * utilizando la función TRANSLATE
     * @param string $campo Nombre del campo a normalizar
     * @return string Expresión SQL para normalizar acentos
     */
    private static function generar_sql_normalizacion_acentos($campo) {
        // Utilizamos la función TRANSLATE de PostgreSQL para reemplazar caracteres acentuados
        return "TRANSLATE($campo, 'áàâäãåéèêëíìîïóòôöõúùûüñÁÀÂÄÃÅÉÈÊËÍÌÎÏÓÒÔÖÕÚÙÛÜÑ', 'aaaaaaaeeeeiiiiooooouuuunAAAAAEEEEIIIIOOOOOUUUUN')";
    }
    
    /**
     * Buscar materias con múltiples filtros combinables
     * Búsqueda case-insensitive y accent-insensitive usando TRANSLATE de PostgreSQL
     * Permite usar _exact al final de un parámetro para buscar coincidencia exacta
     * Los campos depto, area y orientacion usan coincidencia exacta por defecto
     */    static function buscar_materias($filtros = array()) {
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

                // Campos que siempre deben usar coincidencia exacta (pero case-insensitive y accent-insensitive)
                $campos_exactos = array('depto', 'area', 'orientacion');
                
                if (in_array($campo_real, self::$campos_texto)) {
                    // Normalizar acentos para búsqueda accent-insensitive
                    $valor_normalizado = self::normalizar_acentos($valor_latin1);
                    error_log("[DEBUG] Valor normalizado sin acentos: '$valor_normalizado'");
                      // Si es un campo de coincidencia exacta o se especificó _exact
                    if ($busqueda_exacta || in_array($campo_real, $campos_exactos)) {
                        // Para búsqueda exacta pero case y accent insensitive
                        $valor_original_sql = toba::db()->quote($valor_latin1);
                        $valor_sin_acento_sql = toba::db()->quote($valor_normalizado);
                        
                        error_log("[DEBUG] Buscando campo '$campo_real' con valores: original='$valor_latin1', normalizado='$valor_normalizado'");

                        // Especial para campos ISO-8859-1, usamos técnicas específicas para PostgreSQL
                        // Para campos con coincidencia exacta pero case y accent insensitive:
                        // 1. Convertimos a mayúsculas con UPPER para case-insensitive
                        // 2. Usamos varias condiciones para manejar todas las posibles variantes de acentos
                        
                        // Específico para ISO-8859-1 que es lo que usa la base de datos
                        if (in_array($campo_real, array('depto', 'area', 'orientacion'))) {
                            // Para estos campos específicos, usamos una combinación de condiciones para todas las variantes posibles
                            $variantes = array();
                            
                            // 1. El valor exacto como está
                            $variantes[] = "UPPER($campo_real) = UPPER($valor_original_sql)";
                            
                            // 2. Valor sin acentos comparado con campo sin acentos
                            $variantes[] = "UPPER(TRANSLATE($campo_real, 'áàâäãåéèêëíìîïóòôöõúùûüñÁÀÂÄÃÅÉÈÊËÍÌÎÏÓÒÔÖÕÚÙÛÜÑ', 'aaaaaaaeeeeiiiiooooouuuunAAAAAEEEEIIIIOOOOOUUUUN')) = UPPER($valor_sin_acento_sql)";
                            
                            // 3. Para casos específicos como FISICA = Física, agregamos condiciones adicionales
                            // Esto maneja las variantes más comunes en español para estos campos particulares
                            if (strtolower($valor_normalizado) == 'fisica') {
                                $variantes[] = "UPPER($campo_real) IN ('FÍSICA', 'FISICA')";
                            }
                            else if (strtolower($valor_normalizado) == 'quimica') {
                                $variantes[] = "UPPER($campo_real) IN ('QUÍMICA', 'QUIMICA')";
                            }
                            else if (strtolower($valor_normalizado) == 'matematica' || strtolower($valor_normalizado) == 'matematicas') {
                                $variantes[] = "UPPER($campo_real) IN ('MATEMÁTICA', 'MATEMATICA', 'MATEMÁTICAS', 'MATEMATICAS')";
                            }
                            else if (strtolower($valor_normalizado) == 'mecanica') {
                                $variantes[] = "UPPER($campo_real) IN ('MECÁNICA', 'MECANICA')";
                            }
                            else if (strtolower($valor_normalizado) == 'electronica') {
                                $variantes[] = "UPPER($campo_real) IN ('ELECTRÓNICA', 'ELECTRONICA')";
                            }
                            else if (strtolower($valor_normalizado) == 'teorica') {
                                $variantes[] = "UPPER($campo_real) IN ('TEÓRICA', 'TEORICA')";
                            }
                            
                            // Combinamos todas las variantes con OR
                            $where[] = "(" . implode(" OR ", $variantes) . ")";
                        } else {
                            // Para el resto de campos, usamos el método general de accent-insensitive
                            $campo_normalizado = self::generar_sql_normalizacion_acentos($campo_real);
                            $where[] = "(UPPER($campo_real) = UPPER($valor_original_sql) OR 
                                       UPPER($campo_normalizado) = UPPER($valor_sin_acento_sql))";
                        }
                        
                        error_log("[DEBUG] Filtro EXACTO para campo '$campo_real': " . $where[count($where)-1]);
                    } else {                        // Búsqueda parcial para el resto de campos de texto - case y accent insensitive
                        $valor_original_like = toba::db()->quote('%' . $valor_latin1 . '%');
                        $valor_normalizado_like = toba::db()->quote('%' . $valor_normalizado . '%');
                        
                        // Para campos de texto en ISO-8859-1
                        // 1. Búsqueda parcial con ILIKE para case-insensitive
                        // 2. Buscar tanto con versión original como normalizada sin acentos
                        
                        // Campo normalizado para búsqueda accent-insensitive en PostgreSQL
                        $campo_normalizado = "TRANSLATE($campo_real, 'áàâäãåéèêëíìîïóòôöõúùûüñÁÀÂÄÃÅÉÈÊËÍÌÎÏÓÒÔÖÕÚÙÛÜÑ', 'aaaaaaaeeeeiiiiooooouuuunAAAAAEEEEIIIIOOOOOUUUUN')";

                        // Búsqueda parcial case y accent insensitive con condiciones completas
                        $where[] = "($campo_real ILIKE $valor_original_like OR 
                                   $campo_normalizado ILIKE $valor_normalizado_like)";
                        
                        error_log("[DEBUG] Filtro PARCIAL para campo '$campo_real': " . $where[count($where)-1]);
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
     * Búsqueda case-insensitive y accent-insensitive de coincidencia exacta
     */
    static function get_materias_by_depto($depto) {
        // Usamos el método buscar_materias que ahora realiza búsqueda case y accent insensitive
        return self::buscar_materias(array('depto' => $depto));
    }
}

