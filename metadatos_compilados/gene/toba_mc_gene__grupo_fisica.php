<?php

class toba_mc_gene__grupo_fisica
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
  'catedras-3471' => 
  array (
    'padre' => '1',
    'carpeta' => 1,
    'proyecto' => 'catedras',
    'item' => '3471',
    'nombre' => 'DEPARTAMENTOS',
    'orden' => '3',
    'imagen' => NULL,
    'imagen_recurso_origen' => 'apex',
    'es_primer_nivel' => true,
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
  'catedras-3472' => 
  array (
    'padre' => '3471',
    'carpeta' => NULL,
    'proyecto' => 'catedras',
    'item' => '3472',
    'nombre' => 'Aval Programas',
    'orden' => '1',
    'imagen' => NULL,
    'imagen_recurso_origen' => NULL,
    'es_primer_nivel' => false,
  ),
  'catedras-3492' => 
  array (
    'padre' => '3471',
    'carpeta' => 0,
    'proyecto' => 'catedras',
    'item' => '3492',
    'nombre' => 'Aval Planificaciones',
    'orden' => '3',
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
  'catedras-3472' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3472',
  ),
  'catedras-3476' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3476',
  ),
  'catedras-3492' => 
  array (
    'proyecto' => 'catedras',
    'item' => '3492',
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