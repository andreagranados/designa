<?php
class dt_estado_pr extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_estado, descripcion FROM estado_pr ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>