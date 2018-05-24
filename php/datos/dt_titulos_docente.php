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
        function get_titulos_de($id_docente){
            $sql="select * from titulos_docente t_t "
                    . " LEFT JOIN titulo t_i ON (t_t.codc_titul=t_i.codc_titul) "
                    . " where t_t.id_docente=$id_docente";
            return toba::db('designa')->consultar($sql);
        }

}

?>