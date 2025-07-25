<?php

class toba_mc_gene__grupo_docente
{
	static function get_items_menu()
	{
		return array (
  'catedras-2' => 
  array (
    'padre' => '1',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '2',
    'nombre' => 'Inicio',
    'orden' => '0',
    'imagen' => NULL,
    'imagen_recurso_origen' => NULL,
    'es_primer_nivel' => true,
  ),
  'catedras-3468' => 
  array (
    'padre' => '1',
    'carpeta' => 1,
    'proyecto' => 'catedras',
    'item' => '3468',
    'nombre' => 'DOCENTES',
    'orden' => '2',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => true,
  ),
  'catedras-3475' => 
  array (
    'padre' => '1',
    'carpeta' => 1,
    'proyecto' => 'catedras',
    'item' => '3475',
    'nombre' => 'PROGRAMAS y PLANIFICACIONES CRUB',
    'orden' => '5',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => true,
  ),
  'catedras-3498' => 
  array (
    'padre' => '3466',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3498',
    'nombre' => 'Reporte tabla planificaciones',
    'orden' => '6',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => false,
  ),
  'catedras-3469' => 
  array (
    'padre' => '3468',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3469',
    'nombre' => 'Programas',
    'orden' => '1',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => false,
  ),
  'catedras-3488' => 
  array (
    'padre' => '3468',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3488',
    'nombre' => 'Planificaciones',
    'orden' => '2',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => false,
  ),
  'catedras-3476' => 
  array (
    'padre' => '3475',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3476',
    'nombre' => 'Imprimir programas',
    'orden' => '0',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => false,
  ),
  'catedras-3499' => 
  array (
    'padre' => '3475',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3499',
    'nombre' => 'Planificaciones Aprobadas',
    'orden' => '2',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => false,
  ),
);
	}

	static function get_items_accesibles()
	{
		return array (
  'catedras-2' => 
  array (
    'proyecto' => 'catedras',
    'item' => '2',
  ),
  'catedras-3464' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3464',
  ),
  'catedras-3469' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3469',
  ),
  'catedras-3476' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3476',
  ),
  'catedras-3488' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3488',
  ),
  'catedras-3498' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3498',
  ),
  'catedras-3499' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3499',
  ),
);
	}

	static function get_lista_permisos()
	{
		return array (
);
	}

	static function get_membresia()
	{
		return array (
);
	}

}

?>