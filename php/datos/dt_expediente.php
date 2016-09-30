<?php
class dt_expediente extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_exp, nro_exp FROM expediente ORDER BY nro_exp";
		return toba::db('designa')->consultar($sql);
	}


}
?>