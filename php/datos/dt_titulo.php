<?php
class dt_titulo extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codc_titul, desc_titul FROM titulo ORDER BY desc_titul";
		return toba::db('designa')->consultar($sql);
	}

	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['codc_nivel'])) {
			$where[] = "codc_nivel ILIKE ".quote("%{$filtro['codc_nivel']}%");
		}
		if (isset($filtro['desc_titul'])) {
			$where[] = "desc_titul ILIKE ".quote("%{$filtro['desc_titul']}%");
		}
		$sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
		FROM
			titulo as t_t
		ORDER BY codc_nivel";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

}
?>