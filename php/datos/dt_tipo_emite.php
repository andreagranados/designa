<?php
class dt_tipo_emite extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT cod_emite, quien_emite_norma FROM tipo_emite ORDER BY quien_emite_norma";
		return toba::db('designa')->consultar($sql);
	}





















}
?>