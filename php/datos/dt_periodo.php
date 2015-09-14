<?php
class dt_periodo extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_p.id_periodo,
			t_p.descripcion
		FROM
			periodo as t_p
		ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

		function get_descripciones()
		{
			$sql = "SELECT id_periodo, descripcion FROM periodo ORDER BY descripcion";
			return toba::db('designa')->consultar($sql);
		}





}
?>