<?php
class dt_mocovi_tipo_credito extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_tipo_credito, tipo FROM mocovi_tipo_credito ORDER BY tipo";
		return toba::db('designa')->consultar($sql);
	}

}

?>