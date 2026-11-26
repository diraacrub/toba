<?php
/**
 * Que es cada uno en catedras, segun sus perfiles funcionales (grupos de
 * acceso de Toba), y adonde empieza al entrar con la cuenta Huayca.
 *
 * Lo usan la pantalla de inicio (item_inicial.php) y el login Huayca
 * (catedras_huayca_sso): un docente o un departamento que entra a catedras
 * empieza en Gestion Academica, que decide su pantalla (perfiles.destino en
 * deptos): Secretaria, su panel; quien dirige un departamento, el
 * escritorio de su departamento; un docente, Mis catedras. Admin y FADEL se
 * quedan en catedras.
 *
 * Archivo en ASCII.
 */
class catedras_perfiles
{
	const URL_GESTION = 'https://huayca.crub.uncoma.edu.ar/gestion-academica/';

	/** Los grupos de cada departamento (no siempre coinciden con los de Gestion Academica). */
	static $deptos = array('biologiageneral', 'botanica', 'didactica', 'ecologia', 'educacionfisica',
		'enfermeria', 'estadistica', 'explotacionderecursosacuaticos', 'fisica', 'geologiaypetroleo',
		'idiomasextranjerosconpropositosespecificos', 'ingenieriacivil', 'matematica',
		'politicaeducacional', 'psicologia', 'quimica', 'zoologia');
	static $sac = array('sac', 'saccrub', 'sacfadel');

	private $perfiles;

	function __construct(array $perfiles)
	{
		$this->perfiles = array_map('strtolower', $perfiles);
	}

	/** Los del usuario de la sesion. */
	static function actual()
	{
		return new self(toba::usuario()->get_perfiles_funcionales());
	}

	function es_admin()
	{
		return in_array('admin', $this->perfiles);
	}

	function es_sac()
	{
		return count(array_intersect($this->perfiles, self::$sac)) > 0;
	}

	function es_depto()
	{
		return count(array_intersect($this->perfiles, self::$deptos)) > 0;
	}

	/** Docente del CRUB ('docente') o de FADEL ('docentefadel'). */
	function es_docente()
	{
		return in_array('docente', $this->perfiles) || in_array('docentefadel', $this->perfiles);
	}

	/** Docente del CRUB: Gestion Academica es del CRUB, FADEL no tiene Mis catedras. */
	function es_docente_crub()
	{
		return in_array('docente', $this->perfiles);
	}

	/** Secretaria Academica del CRUB: 'sacfadel' es de FADEL. */
	function es_sac_crub()
	{
		return count(array_intersect($this->perfiles, array('sac', 'saccrub'))) > 0;
	}

	/**
	 * true si al entrar empieza en Gestion Academica: Secretaria Academica,
	 * departamento o docente, del CRUB. Admin no: es el trabajo tecnico, y
	 * se queda aca.
	 */
	function empieza_en_gestion()
	{
		if ($this->es_admin()) {
			return false;
		}
		return $this->es_sac_crub() || $this->es_depto() || $this->es_docente_crub();
	}
}
