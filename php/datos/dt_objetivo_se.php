<?php
class dt_objetivo_se extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_obj, '('||id_obj||')'||substr(descripcion,1,30)||'...' as descripcion "
                        . " FROM objetivo_se "
                        . " ORDER BY id_obj";
		return toba::db('designa')->consultar($sql);
	}

}

?>