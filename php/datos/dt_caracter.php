<?php
class dt_caracter extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_car, descripcion FROM caracter ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_desc_renovacion()
	{
		$sql = "SELECT id_car, descripcion "
                        . " FROM caracter "
                        . " WHERE id_car in ('I','S')"
                        . " ORDER BY descripcion asc";
		return toba::db('designa')->consultar($sql);
	}

}
?>