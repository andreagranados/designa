<?php
class dt_norma extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_norma, tipo_norma FROM norma ORDER BY tipo_norma";
		return toba::db('designa')->consultar($sql);
	}
        

        
        function get_norma($id_norma)
        {
            $sql = "SELECT
			t_n.id_norma,
			t_n.nro_norma,
                        t_n.tipo_norma,
			t_n.emite_norma,
			t_n.fecha,
			t_n.pdf
		FROM
			norma as t_n 
                where id_norma=".$id_norma;
            return toba::db('designa')->consultar($sql);
    
        }
        
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['nro_norma'])) {
			$where[] = "nro_norma = ".quote($filtro['nro_norma']);
		}
		if (isset($filtro['tipo_norma'])) {
			$where[] = "tipo_norma = ".quote($filtro['tipo_norma']);
		}
		$sql = "SELECT
			t_n.id_norma,
			t_n.nro_norma,
			t_tne.nombre_tipo as tipo_norma_nombre,
			t_te.quien_emite_norma as emite_norma_nombre,
			t_n.fecha,
			t_n.pdf
		FROM
			norma as t_n	LEFT OUTER JOIN tipo_norma_exp as t_tne ON (t_n.tipo_norma = t_tne.cod_tipo)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_n.emite_norma = t_te.cod_emite)";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

}
?>