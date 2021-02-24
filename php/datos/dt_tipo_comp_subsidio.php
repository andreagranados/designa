<?php
class dt_tipo_comp_subsidio extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_tipo, descripcion FROM tipo_comp_subsidio ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>