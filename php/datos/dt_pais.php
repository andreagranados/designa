<?php
class dt_pais extends toba_datos_tabla
{
	
	function get_descripciones()
	{
		$sql = "SELECT codigo_pais, nombre FROM pais ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}









}
?>