<?php
class dt_plan_estudio extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_plan, cod_plan FROM plan_estudio ORDER BY cod_plan";
		return toba::db('designa')->consultar($sql);
	}

}

?>