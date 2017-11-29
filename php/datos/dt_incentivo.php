<?php
class dt_incentivo extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_inc, descripcion FROM incentivo ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

        function get_listado()
	{
		$sql = "SELECT
			t_i.id_inc,
			t_i.descripcion
		FROM
			incentivo as t_i
		ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>