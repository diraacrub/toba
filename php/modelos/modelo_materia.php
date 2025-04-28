<?php
class modelo_materia {

    // Helper: elimina acentos y pasa a minúsculas
    static function removeAccents($string) {
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
            'Ñ'=>'N', 'ñ'=>'n'
        );
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
    }    // Método para filtrar materias según diversos criterios
    static function get_materias_by_filters($filters, $ids = null) {
        $conditions = array();
        
        // Definimos la cadena de acentos a quitar y sus equivalentes
        $from = 'áàâäãéèêëíìîïóòôöõúùûüñ';
        $to   = 'aaaaaeeeeiiiioooouuuun';

        // Filtro por departamento
        if (isset($filters['departamento'])) {
            $val = self::removeAccents(strtolower($filters['departamento']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "(translate(lower(depto_principal), '$from', '$to') = $val_sql OR translate(lower(depto), '$from', '$to') = $val_sql)";
        }
        
        // Filtro específico por depto_principal
        if (isset($filters['depto_principal'])) {
            $val = self::removeAccents(strtolower($filters['depto_principal']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(depto_principal), '$from', '$to') = $val_sql";
        }

        // Filtro específico por depto 
        if (isset($filters['depto'])) {
            $val = self::removeAccents(strtolower($filters['depto']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(depto), '$from', '$to') = $val_sql";
        }
        
        // Filtro por área
        if (isset($filters['area'])) {
            $val = self::removeAccents(strtolower($filters['area']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(area), '$from', '$to') = $val_sql";
        }
        
        // Filtro por orientación
        if (isset($filters['orientacion'])) {
            $val = self::removeAccents(strtolower($filters['orientacion']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(orientacion), '$from', '$to') = $val_sql";
        }
        
        // Filtro para comprobar si contenidos_mínimos está vacío o no
        if (isset($filters['contenidos_minimos_empty']) && $filters['contenidos_minimos_empty'] === 'true') {
            $conditions[] = "(contenidos_minimos IS NULL OR contenidos_minimos = '')";
        } else if (isset($filters['contenidos_minimos_empty']) && $filters['contenidos_minimos_empty'] === 'false') {
            $conditions[] = "(contenidos_minimos IS NOT NULL AND contenidos_minimos <> '')";
        }
        
        // Búsqueda por contenido en contenidos_mínimos
        if (isset($filters['contenidos_minimos'])) {
            $search = trim($filters['contenidos_minimos']);
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $word_proc = self::removeAccents(strtolower($word));
                $word_sql = toba::db()->quote('%'.$word_proc.'%');
                $conditions[] = "translate(lower(contenidos_minimos), '$from', '$to') LIKE $word_sql";
            }
        }

        // Filtro por nombre de materia
        if (isset($filters['nombre_materia'])) {
            $val = self::removeAccents(strtolower($filters['nombre_materia']));
            $val_sql = toba::db()->quote("%".$val."%");
            $conditions[] = "translate(lower(nombre_materia), '$from', '$to') LIKE $val_sql";
        }

        // Filtro por nombre de carrera
        if (isset($filters['nombre_carrera'])) {
            $val = self::removeAccents(strtolower($filters['nombre_carrera']));
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
            $val = self::removeAccents(strtolower($filters['periodo_plan']));
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
            $val = self::removeAccents(strtolower($filters['optativa']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(optativa), '$from', '$to') = $val_sql";
        }

        // Filtro por trayecto
        if (isset($filters['trayecto'])) {
            $val = self::removeAccents(strtolower($filters['trayecto']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(trayecto), '$from', '$to') = $val_sql";
        }

        // Filtro por código de carrera
        if (isset($filters['cod_carrera'])) {
            $val = self::removeAccents(strtolower($filters['cod_carrera']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(cod_carrera), '$from', '$to') = $val_sql";
        }

        // Filtro por plan_guarani
        if (isset($filters['plan_guarani'])) {
            $val = self::removeAccents(strtolower($filters['plan_guarani']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(plan_guarani), '$from', '$to') = $val_sql";
        }

        // Filtro por código guaraní
        if (isset($filters['cod_guarani'])) {
            $val = self::removeAccents(strtolower($filters['cod_guarani']));
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
