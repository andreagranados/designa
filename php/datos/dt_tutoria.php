<?php
class dt_tutoria extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_t.id_tutoria,
			t_t.descripcion,
			t_ua.descripcion as uni_acad_nombre
		FROM
			tutoria as t_t	LEFT OUTER JOIN unidad_acad as t_ua ON (t_t.uni_acad = t_ua.sigla)
		ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}



	function get_descripciones()
	{
		$sql = "SELECT id_tutoria, descripcion FROM tutoria ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>