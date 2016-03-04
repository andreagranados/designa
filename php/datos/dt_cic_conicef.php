<?php
class dt_cic_conicef extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id, descripcion FROM cic_conicef ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

















	function get_listado()
	{
		$sql = "SELECT
			t_cc.id,
			t_cc.descripcion
		FROM
			cic_conicef as t_cc
		ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>