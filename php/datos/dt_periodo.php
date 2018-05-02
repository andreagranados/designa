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
		$sql = "SELECT id_periodo, descripcion FROM periodo ORDER BY id_periodo";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripciones_sin_ambos()
	{
		$sql = "SELECT id_periodo, descripcion FROM periodo WHERE id_periodo<>4"
                        . " ORDER BY id_periodo";
		return toba::db('designa')->consultar($sql);
	}
}
?>