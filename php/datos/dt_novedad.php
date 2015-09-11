<?php
class dt_novedad extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_novedad, tipo_norma FROM novedad ORDER BY tipo_norma";
		return toba::db('designa')->consultar($sql);
	}
        function get_listado()
	{
		$sql = "SELECT id_novedad, tipo_norma FROM novedad ORDER BY tipo_norma";
		return toba::db('designa')->consultar($sql);
	}
         function get_novedades_desig($des)
	{
		$where=" WHERE id_designacion=".$des;
                $sql = "SELECT t_n.id_designacion,t_n.id_novedad,t_n.desde,t_n.hasta,t_d.desc_corta as tipo_nov,t_x.nombre_tipo as tipo_emite,t_e.quien_emite_norma as tipo_norma,t_n.norma_legal"
                        . " FROM novedad t_n LEFT OUTER JOIN tipo_emite t_e ON (t_n.tipo_emite=t_e.cod_emite) LEFT OUTER JOIN tipo_norma_exp t_x ON(t_x.cod_tipo=t_n.tipo_norma) "
                        . " LEFT OUTER JOIN tipo_novedad t_d ON (t_n.tipo_nov=t_d.id_tipo) $where order by t_n.desde";
		return toba::db('designa')->consultar($sql);
	}
}

?>