<?php
class modelo_programa {

    // Función para traer todos los programas junto con los datos de la materia asociada
    static function get_programas_con_materia() {
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia";
        return toba::db()->consultar($sql);
    }

    // Función para traer un programa en particular junto con su materia, dado su id
    static function get_programa_con_materia($id_programa) {
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.id_programa = " . quote($id_programa);
        return toba::db()->consultar_fila($sql);
    }

    // Función para traer varios programas dado un arreglo de IDs
    static function get_programas_por_ids($ids) {
        // Aseguramos que cada id sea un entero
        $ids = array_map('intval', $ids);
        $lista_ids = implode(",", $ids);
        $sql = "SELECT p.*, m.* 
                FROM programas p 
                LEFT JOIN materias m ON p.id_materia_prog = m.id_materia
                WHERE p.id_programa IN ($lista_ids)";
        return toba::db()->consultar($sql);
    }
}
