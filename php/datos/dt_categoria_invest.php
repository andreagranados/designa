<?php
class dt_categoria_invest extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT cod_cati, descripcion FROM categoria_invest ORDER BY cod_cati";
		return toba::db('designa')->consultar($sql);
	}

}

?>