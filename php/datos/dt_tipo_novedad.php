<?php
class dt_tipo_novedad extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_tipo, descripcion FROM tipo_novedad ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>