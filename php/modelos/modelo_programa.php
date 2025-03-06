<?php
class modelo_programa {

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

    // Función para traer todos los programas junto con los datos de la materia asociada (información completa)
    static function get_programas_con_materia() {
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.estado = 'aprobado'";
        return toba::db()->consultar($sql);
    }

    // Función para traer un programa en particular junto con su materia, dado su id
    static function get_programa_con_materia($id_programa) {
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.id_programa = " . quote($id_programa) . " AND p.estado = 'aprobado'";
        return toba::db()->consultar_fila($sql);
    }

    // Función para traer varios programas dado un arreglo de IDs (información completa)
    static function get_programas_por_ids($ids) {
        $ids = array_map('intval', $ids);
        $lista_ids = implode(",", $ids);
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.id_programa IN ($lista_ids) AND p.estado = 'aprobado'";
        return toba::db()->consultar($sql);
    }

    // Función para traer todos los programas con información reducida:
    // - De programas: id_programa y ano_academico.
    // - De materias: cod_guarani, cod_carrera, nombre_materia, plan_guarani y plan_ordenanzas.
    static function get_programas_reducidos() {
        $sql = "SELECT p.id_programa, p.ano_academico, m.cod_guarani, m.cod_carrera, m.nombre_materia, m.plan_guarani, m.plan_ordenanzas
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.estado = 'aprobado'";
        return toba::db()->consultar($sql);
    }

    // Función para traer varios programas dado un arreglo de IDs con información reducida
    static function get_programas_reducidos_por_ids($ids) {
        $ids = array_map('intval', $ids);
        $lista_ids = implode(",", $ids);
        $sql = "SELECT p.id_programa, p.ano_academico, m.cod_guarani, m.cod_carrera, m.nombre_materia, m.plan_guarani, m.plan_ordenanzas
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.id_programa IN ($lista_ids) AND p.estado = 'aprobado'";
        return toba::db()->consultar($sql);
    }

    // Método para armar dinámicamente la cláusula WHERE para filtros (información completa)
    static function get_programas_con_materia_by_filters($filters, $ids = null) {
        $conditions = array();
        // Aseguramos que solo se devuelvan programas aprobados
        $conditions[] = "p.estado = 'aprobado'";

        // Definimos la cadena de acentos a quitar y sus equivalentes
        $from  = 'áàâäãéèêëíìîïóòôöõúùûüñ';
        $to    = 'aaaaaeeeeiiiioooouuuun';

        // Para cada filtro textual procesamos el valor en PHP
        if (isset($filters['ano_academico'])) {
            $val = self::removeAccents(strtolower($filters['ano_academico']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(p.ano_academico), '$from', '$to') = $val_sql";
        }
        if (isset($filters['cod_carrera'])) {
            $val = self::removeAccents(strtolower($filters['cod_carrera']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.cod_carrera), '$from', '$to') = $val_sql";
        }
        if (isset($filters['nombre_materia'])) {
            $val = self::removeAccents(strtolower($filters['nombre_materia']));
            $val_sql = toba::db()->quote("%".$val."%");
            $conditions[] = "translate(lower(m.nombre_materia), '$from', '$to') LIKE $val_sql";
        }
        if (isset($filters['horas_semanales'])) {
            $hs = toba::db()->quote($filters['horas_semanales']);
            $conditions[] = "m.horas_semanales = $hs";
        }
        if (isset($filters['depto_principal'])) {
            $val = self::removeAccents(strtolower($filters['depto_principal']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.depto_principal), '$from', '$to') = $val_sql";
        }
        if (isset($filters['depto'])) {
            $val = self::removeAccents(strtolower($filters['depto']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.depto), '$from', '$to') = $val_sql";
        }
        if (isset($filters['area'])) {
            $val = self::removeAccents(strtolower($filters['area']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.area), '$from', '$to') = $val_sql";
        }
        if (isset($filters['orientacion'])) {
            $val = self::removeAccents(strtolower($filters['orientacion']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.orientacion), '$from', '$to') = $val_sql";
        }
        if (isset($filters['contenidos_minimos'])) {
            // Búsqueda flexible: se divide el texto en palabras, se procesa cada una y se genera condición AND
            $search = trim($filters['contenidos_minimos']);
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $word_proc = self::removeAccents(strtolower($word));
                $word_sql = toba::db()->quote('%'.$word_proc.'%');
                $conditions[] = "translate(lower(m.contenidos_minimos), '$from', '$to') LIKE $word_sql";
            }
        }
        if ($ids) {
            $ids = array_map('intval', $ids);
            $lista_ids = implode(",", $ids);
            $conditions[] = "p.id_programa IN ($lista_ids)";
        }
        $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                $where";
        return toba::db()->consultar($sql);
    }

    // Método para armar dinámicamente la cláusula WHERE para filtros (información reducida)
    static function get_programas_reducidos_by_filters($filters, $ids = null) {
        $conditions = array();
        // Aseguramos que solo se devuelvan programas aprobados
        $conditions[] = "p.estado = 'aprobado'";

        $from  = 'áàâäãéèêëíìîïóòôöõúùûüñ';
        $to    = 'aaaaaeeeeiiiioooouuuun';

        if (isset($filters['ano_academico'])) {
            $val = self::removeAccents(strtolower($filters['ano_academico']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(p.ano_academico), '$from', '$to') = $val_sql";
        }
        if (isset($filters['cod_carrera'])) {
            $val = self::removeAccents(strtolower($filters['cod_carrera']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.cod_carrera), '$from', '$to') = $val_sql";
        }
        if (isset($filters['nombre_materia'])) {
            $val = self::removeAccents(strtolower($filters['nombre_materia']));
            $val_sql = toba::db()->quote("%".$val."%");
            $conditions[] = "translate(lower(m.nombre_materia), '$from', '$to') LIKE $val_sql";
        }
        if (isset($filters['horas_semanales'])) {
            $hs = toba::db()->quote($filters['horas_semanales']);
            $conditions[] = "m.horas_semanales = $hs";
        }
        if (isset($filters['depto_principal'])) {
            $val = self::removeAccents(strtolower($filters['depto_principal']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.depto_principal), '$from', '$to') = $val_sql";
        }
        if (isset($filters['depto'])) {
            $val = self::removeAccents(strtolower($filters['depto']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.depto), '$from', '$to') = $val_sql";
        }
        if (isset($filters['area'])) {
            $val = self::removeAccents(strtolower($filters['area']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.area), '$from', '$to') = $val_sql";
        }
        if (isset($filters['orientacion'])) {
            $val = self::removeAccents(strtolower($filters['orientacion']));
            $val_sql = toba::db()->quote($val);
            $conditions[] = "translate(lower(m.orientacion), '$from', '$to') = $val_sql";
        }
        if (isset($filters['contenidos_minimos'])) {
            $search = trim($filters['contenidos_minimos']);
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $word_proc = self::removeAccents(strtolower($word));
                $word_sql = toba::db()->quote('%'.$word_proc.'%');
                $conditions[] = "translate(lower(m.contenidos_minimos), '$from', '$to') LIKE $word_sql";
            }
        }
        if ($ids) {
            $ids = array_map('intval', $ids);
            $lista_ids = implode(",", $ids);
            $conditions[] = "p.id_programa IN ($lista_ids)";
        }
        $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT p.id_programa, p.ano_academico, m.cod_guarani, m.cod_carrera, m.nombre_materia, m.plan_guarani, m.plan_ordenanzas
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                $where";
        return toba::db()->consultar($sql);
    }
}
