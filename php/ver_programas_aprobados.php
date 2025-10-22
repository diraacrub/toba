<?php
class ver_programas_aprobados extends catedras_ci
{
	// en misma ventana
	function ini()
	{
		echo '<script type="text/javascript">
			window.location.href = "https://huayca.crub.uncoma.edu.ar/programas/";
		</script>';
	}
	
	// en otra ventana
	function ini_ov()
	{
		echo '<script type="text/javascript">
			window.open("https://huayca.crub.uncoma.edu.ar/programas/", "_blank");
		</script>';
	}


}
?>