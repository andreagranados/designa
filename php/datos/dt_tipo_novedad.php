<?php
class dt_tipo_novedad extends toba_datos_tabla
{
	function get_descripciones_lic()
	{
		$sql = "SELECT id_tipo, descripcion FROM tipo_novedad WHERE id_tipo=2 or id_tipo=3 or id_tipo=5 ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripciones_bajas()
	{
		$sql = "SELECT id_tipo, descripcion FROM tipo_novedad WHERE id_tipo=1 or id_tipo=4 ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
	function get_descripciones()
	{
		$sql = "SELECT id_tipo, descripcion FROM tipo_novedad ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>