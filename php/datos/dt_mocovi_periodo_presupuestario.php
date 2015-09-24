<?php
class dt_mocovi_periodo_presupuestario extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_periodo,  FROM mocovi_periodo_presupuestario ORDER BY ";
		return toba::db('designa')->consultar($sql);
	}
        function get_anios()
	{
		$sql = "SELECT distinct anio  FROM mocovi_periodo_presupuestario ORDER BY anio";
		return toba::db('designa')->consultar($sql);
	}

}

?>