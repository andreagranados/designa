<?php
class dt_funcion_extension extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_extension, descripcion FROM funcion_extension ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>