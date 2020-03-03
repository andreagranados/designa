<?php
class dt_institucion extends designa_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_institucion, nombre_institucion FROM institucion ORDER BY nombre_institucion";
		return toba::db('designa')->consultar($sql);
	}

}
?>