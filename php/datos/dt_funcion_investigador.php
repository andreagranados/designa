<?php
class dt_funcion_investigador extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_funcion, descripcion FROM funcion_investigador ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        

}

?>