<?php
class dt_tutoria extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['descripcion'])) {
			$where[] = "descripcion ILIKE ".quote("%{$filtro['descripcion']}%");
		}
		if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
		$sql = "SELECT
			t_t.id_tutoria,
			t_t.descripcion,
			t_ua.descripcion as uni_acad_nombre
		FROM
			tutoria as t_t	LEFT OUTER JOIN unidad_acad as t_ua ON (t_t.uni_acad = t_ua.sigla)
		ORDER BY descripcion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}


}
?>