<?php
class dt_titulos_docente extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_td.id_docente,
			t_td.codc_titul,
                        t_t.desc_titul,
                        t_i.desc_item as nivel,
			t_td.fec_emisi,
			t_td.fec_finalizacion
		FROM
			titulos_docente as t_td LEFT OUTER JOIN titulo as t_t ON (t_td.codc_titul=t_t.codc_titul) LEFT OUTER JOIN tipo as t_i ON (t_td.nro_tab3=t_i.nro_tabla and t_td.codc_nivel=t_i.desc_abrev";
		
                return toba::db('designa')->consultar($sql);
               
	}

}

?>