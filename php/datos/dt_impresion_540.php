<?php
class dt_impresion_540 extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id, id FROM impresion_540 ORDER BY id";
		return toba::db('designa')->consultar($sql);
	}














}
?>