<?php
class dt_escalafon extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_escalafon, descripcion FROM escalafon ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>