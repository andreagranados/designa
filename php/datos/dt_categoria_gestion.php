<?php
class dt_categoria_gestion extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codigo, descripcion FROM categoria_gestion ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}


}
?>