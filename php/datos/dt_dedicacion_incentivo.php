<?php
class dt_dedicacion_incentivo extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_di, descripcion FROM dedicacion_incentivo ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

















}
?>