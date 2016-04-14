<?php
class dt_tipo_norma_exp extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_tne.cod_tipo,
			t_tne.nombre_tipo
		FROM
			tipo_norma_exp as t_tne
		ORDER BY nombre_tipo";
		return toba::db('designa')->consultar($sql);
	}

	function get_descripciones()
	{
		$sql = "SELECT cod_tipo, nombre_tipo FROM tipo_norma_exp ORDER BY nombre_tipo";
		return toba::db('designa')->consultar($sql);
	}






}
?>