<?php
class dt_mocovi_programa extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_programa, nombre FROM mocovi_programa ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}

}

?>