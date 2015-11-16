<?php
class dt_pextension extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
		$sql = "SELECT
			t_p.id_pext,
			t_p.codigo,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fecha_resol,
			t_te.quien_emite_norma as emite_tipo,
			t_ua.descripcion as uni_acad,
			t_p.fec_desde,
			t_p.fec_hasta
		FROM
			pextension as t_p	
                        LEFT OUTER JOIN tipo_emite as t_te ON (t_p.emite_tipo = t_te.cod_emite)
			LEFT OUTER JOIN unidad_acad as t_ua ON (t_p.uni_acad = t_ua.sigla)
		ORDER BY denominacion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
               
		return toba::db('designa')->consultar($sql);
	}

}

?>