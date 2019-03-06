<?php
class dt_dedicacion extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_ded, descripcion FROM dedicacion ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>