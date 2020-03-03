<?php
class dt_rubro_presupuesto extends toba_datos_tabla
{
    

	function get_descripciones()
	{
		$sql = "SELECT id_rubro, descripcion FROM rubro_presupuesto ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>