<?php
class dt_tipo_designacion extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id, descripcion FROM tipo_designacion ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}












}
?>