<?php
class dt_tipo_convocatoria extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id, descripcion FROM tipo_convocatoria ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>