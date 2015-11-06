<?php
class dt_categ_estatuto extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codigo_est, descripcion FROM categ_estatuto ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}














}
?>