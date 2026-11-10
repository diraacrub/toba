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

    // ------------------------------------------------------------------
    // Programas del formulario integrado (operacion "Programas y
    // Planificaciones", ci_programas_y_planif).
    //
    // El programa se guarda en la misma fila de 'programas' que el
    // formulario separado, con el mismo circuito de estados, asi que la API
    // lo trata igual. La unica diferencia de formato es equipo_catedra: el
    // formulario integrado lo guarda como JSON, una fila por docente
    // [apellido, nombre, legajo, cargo, dedicacion] (sin el responsable,
    // que sigue en apellido_resp/nombre_resp/cargo_resp).
    //
    // Para que los consumidores sigan recibiendo lo de siempre, la API lo
    // devuelve como texto, un docente por linea ("APELLIDO, NOMBRE - PAD3"),
    // y agrega:
    //   - formulario: "integrado" | "separado"
    //   - equipo_catedra_lista: [{apellido, nombre, cargo, dedicacion}] en
    //     los integrados (sin legajos), null en los separados.
    // Los datos vienen de la base en LATIN1, como el resto de los campos.
    // ------------------------------------------------------------------

    static function equipo_desde_json($valor) {
        if (!is_string($valor) || substr(ltrim($valor), 0, 1) !== '[') {
            return null;
        }
        $filas = json_decode(mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1'), true);
        return is_array($filas) ? $filas : null;
    }

    static function normalizar_programa($programa) {
        if (!is_array($programa) || !array_key_exists('equipo_catedra', $programa)) {
            return $programa;
        }
        $filas = self::equipo_desde_json($programa['equipo_catedra']);
        if ($filas === null) {
            $programa['formulario'] = 'separado';
            $programa['equipo_catedra_lista'] = null;
            return $programa;
        }
        $lineas = array();
        $lista = array();
        foreach ($filas as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $apellido = trim((string) ($fila[0] ?? ''));
            $nombre = trim((string) ($fila[1] ?? ''));
            $cargo = trim((string) ($fila[3] ?? ''));
            $dedicacion = trim((string) ($fila[4] ?? ''));
            if ($apellido === '' && $nombre === '') {
                continue;
            }
            $linea = $apellido . ($nombre !== '' ? ', ' . $nombre : '');
            if ($cargo !== '' || $dedicacion !== '') {
                $linea .= ' - ' . $cargo . $dedicacion;
            }
            $lineas[] = mb_convert_encoding($linea, 'ISO-8859-1', 'UTF-8');
            $lista[] = array(
                'apellido' => mb_convert_encoding($apellido, 'ISO-8859-1', 'UTF-8'),
                'nombre' => mb_convert_encoding($nombre, 'ISO-8859-1', 'UTF-8'),
                'cargo' => $cargo,
                'dedicacion' => $dedicacion,
            );
        }
        $programa['equipo_catedra'] = implode("\n", $lineas);
        $programa['formulario'] = 'integrado';
        $programa['equipo_catedra_lista'] = $lista;
        return $programa;
    }

    static function normalizar_programas($programas) {
        if (!is_array($programas)) {
            return $programas;
        }
        return array_map(array('modelo_programa', 'normalizar_programa'), $programas);
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
