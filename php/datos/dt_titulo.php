<?php
class dt_titulo extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codc_titul, desc_titul FROM titulo ORDER BY desc_titul";
		return toba::db('designa')->consultar($sql);
	}

	function get_listado()
	{
		$sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
		FROM
			titulo as t_t
		ORDER BY codc_nivel,desc_titul";
		return toba::db('designa')->consultar($sql);
	}
        

}
?>