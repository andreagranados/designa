<?php
class dt_estado_presupuesto extends designa_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_estado, descripcion FROM estado_presupuesto ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>