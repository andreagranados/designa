<?php
class dt_ods extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_ods, descripcion FROM ods ORDER BY id_ods";
		return toba::db('designa')->consultar($sql);
	}

}
?>