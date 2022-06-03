<?php
class dt_categoria_mincyt extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT * FROM categoria_mincyt ORDER BY id_cat";
		return toba::db('designa')->consultar($sql);
	}

}

?>