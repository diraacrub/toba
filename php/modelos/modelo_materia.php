<?php
class modelo_materia {
    
    // Helper: elimina acentos y maneja caracteres Unicode
    static function removeAccents($string) {
        if (!is_string($string)) {
            return $string;
        }
        
        // Asegurar que estamos trabajando con UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8');
        }
        
        // Mapa de caracteres Unicode con acentos a sus equivalentes sin acentos
        $unwanted_array = array(
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ä'=>'A', 'Ã'=>'A',
            'á'=>'a', 'à'=>'a', 'â'=>'a', 'ä'=>'a', 'ã'=>'a',
            'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
            'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I',
            'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
            'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Ö'=>'O', 'Õ'=>'O',
            'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'ö'=>'o', 'õ'=>'o',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u',
            'Ñ'=>'N', 'ñ'=>'n',
            // Caracteres Unicode adicionales representados en formato JSON como \uXXXX
            "\u00C1"=>'A', "\u00C0"=>'A', "\u00C2"=>'A', "\u00C4"=>'A', "\u00C3"=>'A',
            "\u00E1"=>'a', "\u00E0"=>'a', "\u00E2"=>'a', "\u00E4"=>'a', "\u00E3"=>'a',
            "\u00C9"=>'E', "\u00C8"=>'E', "\u00CA"=>'E', "\u00CB"=>'E',
            "\u00E9"=>'e', "\u00E8"=>'e', "\u00EA"=>'e', "\u00EB"=>'e',
            "\u00CD"=>'I', "\u00CC"=>'I', "\u00CE"=>'I', "\u00CF"=>'I',
            "\u00ED"=>'i', "\u00EC"=>'i', "\u00EE"=>'i', "\u00EF"=>'i',
            "\u00D3"=>'O', "\u00D2"=>'O', "\u00D4"=>'O', "\u00D6"=>'O', "\u00D5"=>'O',
            "\u00F3"=>'o', "\u00F2"=>'o', "\u00F4"=>'o', "\u00F6"=>'o', "\u00F5"=>'o',
            "\u00DA"=>'U', "\u00D9"=>'U', "\u00DB"=>'U', "\u00DC"=>'U',
            "\u00FA"=>'u', "\u00F9"=>'u', "\u00FB"=>'u', "\u00FC"=>'u',
            "\u00D1"=>'N', "\u00F1"=>'n'
        );
        
        // Si estamos tratando con los códigos de escapado JSON (\uXXXX)
        if (strpos($string, '\u') !== false) {
            // Convertir \uXXXX a caracteres reales
            $string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16BE');
            }, $string);
        }
        
        return strtr($string, $unwanted_array);
    }

    // Función para traer todas las materias 
    static function get_all_materias() {
        $sql = "SELECT * FROM materias";
        return toba::db()->consultar($sql);
    }

    // Función para traer una materia por su ID
    static function get_materia_by_id($id_materia) {
        $sql = "SELECT * FROM materias WHERE id_materia = " . quote($id_materia);
        return toba::db()->consultar_fila($sql);
    }

    // Función para traer varias materias dada una lista de IDs
    static function get_materias_by_ids($ids) {
        $ids = array_map('intval', $ids);
        $lista_ids = implode(",", $ids);
        $sql = "SELECT * FROM materias WHERE id_materia IN ($lista_ids)";
        return toba::db()->consultar($sql);
    }
    
    // Método para filtrar materias según diversos criterios
    static function get_materias_by_filters($filters, $ids = null) {
        $conditions = array();
          // Definimos la cadena de acentos a quitar y sus equivalentes
        $from = 'áàâäãéèêëíìîïóòôöõúùûüñÁÀÂÄÃÉÈÊËÍÌÎÏÓÒÔÖÕÚÙÛÜÑ';
        $to   = 'aaaaaeeeeiiiioooouuuunAAAAAEEEEIIIIOOOOOUUUUN';
        
        // Filtro por departamento
        if (isset($filters['departamento'])) {
            $val = self::removeAccents(mb_strtolower($filters['departamento'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "(translate(lower(depto_principal), '$from', '$to') = $val_sql OR translate(lower(depto), '$from', '$to') = $val_sql)";
        }
        
        // Filtro específico por depto_principal
        if (isset($filters['depto_principal'])) {
            $val = self::removeAccents(mb_strtolower($filters['depto_principal'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(depto_principal), '$from', '$to') = $val_sql";
        }

        // Filtro específico por depto 
        if (isset($filters['depto'])) {
            $val = self::removeAccents(mb_strtolower($filters['depto'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(depto), '$from', '$to') = $val_sql";
        }
        
        // Filtro por área
        if (isset($filters['area'])) {
            $val = self::removeAccents(mb_strtolower($filters['area'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(area), '$from', '$to') = $val_sql";
        }
        
        // Filtro por orientación
        if (isset($filters['orientacion'])) {
            $val = self::removeAccents(mb_strtolower($filters['orientacion'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(orientacion), '$from', '$to') = $val_sql";
        }
        
        // Filtro para comprobar si contenidos_mínimos está vacío o no
        if (isset($filters['contenidos_minimos_empty']) && $filters['contenidos_minimos_empty'] === 'true') {
            $conditions[] = "(contenidos_minimos IS NULL OR TRIM(contenidos_minimos) = '')";
        } else if (isset($filters['contenidos_minimos_empty']) && $filters['contenidos_minimos_empty'] === 'false') {
            $conditions[] = "(contenidos_minimos IS NOT NULL AND TRIM(contenidos_minimos) <> '')";
        }
        
        // Búsqueda por contenido en contenidos_mínimos
        if (isset($filters['contenidos_minimos'])) {
            $search = trim($filters['contenidos_minimos']);
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $word_proc = self::removeAccents(mb_strtolower($word, 'UTF-8'));
                $word_sql = toba::db()->quote('%'.$word_proc.'%');
                $conditions[] = "translate(lower(contenidos_minimos), '$from', '$to') LIKE $word_sql";
            }
        }

        // Filtro por nombre de materia
        if (isset($filters['nombre_materia'])) {
            $val = self::removeAccents(mb_strtolower($filters['nombre_materia'], 'UTF-8'));
            $val_sql = toba::db()->quote("%".$val."%");
            $conditions[] = "translate(lower(nombre_materia), '$from', '$to') LIKE $val_sql";
        }

        // Filtro por nombre de carrera
        if (isset($filters['nombre_carrera'])) {
            $val = self::removeAccents(mb_strtolower($filters['nombre_carrera'], 'UTF-8'));
            $val_sql = toba::db()->quote("%".$val."%");
            $conditions[] = "translate(lower(nombre_carrera), '$from', '$to') LIKE $val_sql";
        }

        // Filtro por año del plan
        if (isset($filters['ano_plan'])) {
            $val = intval($filters['ano_plan']);
            $conditions[] = "ano_plan = $val";
        }
        
        // Filtro por periodo del plan
        if (isset($filters['periodo_plan'])) {
            $val = self::removeAccents(mb_strtolower($filters['periodo_plan'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(periodo_plan), '$from', '$to') = $val_sql";
        }

        // Filtro por horas totales
        if (isset($filters['horas_totales'])) {
            $val = intval($filters['horas_totales']);
            $conditions[] = "horas_totales = $val";
        }

        // Filtro por horas semanales
        if (isset($filters['horas_semanales'])) {
            $val = intval($filters['horas_semanales']);
            $conditions[] = "horas_semanales = $val";
        }

        // Filtro por optativa
        if (isset($filters['optativa'])) {
            $val = self::removeAccents(mb_strtolower($filters['optativa'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(optativa), '$from', '$to') = $val_sql";
        }
        
        // Filtro por trayecto
        if (isset($filters['trayecto'])) {
            $val = self::removeAccents(mb_strtolower($filters['trayecto'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(trayecto), '$from', '$to') = $val_sql";
        }

        // Filtro por código de carrera
        if (isset($filters['cod_carrera'])) {
            $val = self::removeAccents(mb_strtolower($filters['cod_carrera'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(cod_carrera), '$from', '$to') = $val_sql";
        }

        // Filtro por plan_guarani
        if (isset($filters['plan_guarani'])) {
            $val = self::removeAccents(mb_strtolower($filters['plan_guarani'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(plan_guarani), '$from', '$to') = $val_sql";
        }

        // Filtro por código guaraní
        if (isset($filters['cod_guarani'])) {
            $val = self::removeAccents(mb_strtolower($filters['cod_guarani'], 'UTF-8'));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(cod_guarani), '$from', '$to') = $val_sql";
        }

        // Filtrar por IDs específicos
        if ($ids) {
            $ids = array_map('intval', $ids);
            $lista_ids = implode(",", $ids);
            $conditions[] = "id_materia IN ($lista_ids)";
        }

        $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT * FROM materias $where";
        return toba::db()->consultar($sql);
    }
}
